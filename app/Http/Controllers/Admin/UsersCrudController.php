<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GroceryCrud\Core\GroceryCrud;
use App\Http\Controllers\Admin\Concerns\BuildsCrud;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersCrudController extends Controller
{
    use BuildsCrud;

    public function index(Request $request)
    {
        $viewData = $this->renderCrud($request, function (GroceryCrud $crud) {

            // Tabla y sujeto
            $crud->setTable('users');
            $crud->setSubject('User', 'Users');

            $crud->unsetExport();
            $crud->unsetPrint();
            $crud->unsetColumnsButton();
            $crud->unsetSettings();
            $crud->unsetFilters();
            $crud->unsetAdd();
            $crud->unsetEdit();
            $crud->unsetDelete();

            // Columnas visibles (evitar sensibles/largos)
            $cols = [
                'id', 'name', 'email', 'username', 'company', 'created_at'
            ];
            if (!Schema::hasColumn('users','created_at')) $cols = array_diff($cols, ['created_at']);
            if (!Schema::hasColumn('users','email_verified_at')) $cols = array_diff($cols, ['email_verified_at']);
            $crud->columns(array_values($cols));

            // Campos del formulario
            $crud->fields([
                'name', 'username', 'email', 'company', 'business_type', 'phone',
                'role', 'plan_id',
                'password',                      // se hashea en callbacks
                'email_verified_at',             // opcional marcar verificación
                'subscription_end',
                'current_location', 'current_location_file',
                'agree'
                // Nota: local_db, remember_token, last_sync se omiten del form
            ]);

            // Reglas
            $crud->requiredFields(['name', 'username', 'email', 'role']);
            $crud->uniqueFields(['username', 'email']);

            // Labels
            $crud->displayAs([
                'id'                    => 'ID',
                'name'                  => 'Name',
                'username'              => 'Username',
                'email'                 => 'Email',
                'company'               => 'Company',
                'business_type'         => 'Business Type',
                'phone'                 => 'Phone',
                'email_verified_at'     => 'Email Verified At',
                'role'                  => 'Role',
                'password'              => 'Password',
                'remember_token'        => 'Remember Token',
                'created_at'            => 'Joined At',
                'updated_at'            => 'Updated At',
                'local_db'              => 'Local DB',
                'current_location'      => 'Current Location',
                'current_location_file' => 'Location File',
                'last_sync'             => 'Last Sync',
                'plan_id'               => 'Plan',
                'subscription_end'      => 'Subscription End',
                'agree'                 => 'Accept Terms',
            ]);

            // Tipos de campo / UI
            $crud->fieldType('role', 'dropdown', [
                1 => 'Admin',
                2 => 'Manager',
                3 => 'Member',
            ]);

            $crud->fieldType('agree', 'dropdown', [
                0 => 'No',
                1 => 'Yes',
            ]);

            $crud->fieldType('password', 'password');

            $crud->setActionButton(
                'View',
                'fa fa-eye', // usa tu pack de iconos; si es remix/feather cámbialo
                fn($row) => route('admin.users.view', $row->id),
                false
            );

            // Relación plan_id → plans.name
            if (Schema::hasTable('plans')) {
                $display = Schema::hasColumn('plans','name') ? 'name' : (Schema::hasColumn('plans','id') ? 'id' : null);
                if ($display) $crud->setRelation('plan_id', 'plans', $display);
            }

            // Ocultar campos en tabla
            $crud->unsetColumns(['password', 'remember_token', 'local_db', 'updated_at', 'last_sync', 'current_location_file']);

            // Orden
            $crud->defaultOrdering('id', 'desc');

            // Badges para verificación
            if (Schema::hasColumn('users','email_verified_at')) {
                $crud->callbackColumn('email_verified_at', function ($value) {
                    if (!empty($value)) {
                        return '<span class="badge bg-success">Verified</span> <small>'.e($value).'</small>';
                    }
                    return '<span class="badge bg-secondary">Pending</span>';
                });
            }

            // Normalizaciones + Hash de password
            $crud->callbackBeforeInsert(function ($state) {
                foreach (['name','username','email','company','business_type','phone'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                // hash obligatorio si fue proporcionado
                if (!empty($state->data['password'])) {
                    $state->data['password'] = Hash::make($state->data['password']);
                } else {
                    unset($state->data['password']);
                }
                return $state;
            });

            $crud->callbackBeforeUpdate(function ($state) {
                foreach (['name','username','email','company','business_type','phone'] as $k) {
                    if (isset($state->data[$k])) $state->data[$k] = trim((string)$state->data[$k]);
                }
                // solo re-hash si se capturó algo
                if (isset($state->data['password'])) {
                    if ($state->data['password'] === '' || $state->data['password'] === null) {
                        unset($state->data['password']); // no tocar el actual
                    } else {
                        $state->data['password'] = Hash::make($state->data['password']);
                    }
                }
                return $state;
            });

            // Validación ligera de email
            $crud->callbackBeforeInsert(function ($state) {
                if (isset($state->data['email']) && !filter_var($state->data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception('Invalid email format.');
                }
                return $state;
            });
            $crud->callbackBeforeUpdate(function ($state) {
                if (isset($state->data['email']) && !filter_var($state->data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception('Invalid email format.');
                }
                return $state;
            });
        });

        if ($viewData instanceof \Illuminate\Http\Response) {
            return $viewData;
        }

        // Usa tus blades subidos
        // resources/views/admin/users/index.blade.php  (lista)
        // resources/views/admin/users/details.blade.php (si lo usas para show)
        return view('admin.users.users', $viewData);
    }

    public function view($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            abort(404);
        }

        // Plan (por si el blade lo muestra)
        if (Schema::hasTable('plans')) {
            $user->plan = DB::table('plans')
                ->select('id','name','type','price','time_unit','project_allowed')
                ->where('id', $user->plan_id)
                ->first();
            // Alias común por si el blade espera plan_name
            $user->plan_name = $user->plan->name ?? null;
        }

        // Hidrata relaciones comunes que el blade suele usar
        // (si alguna tabla no existe, devolvemos colección vacía)
        $user->contacts   = Schema::hasTable('contacts')
            ? DB::table('contacts')->where('user_id', $id)->get()
            : collect();

        $user->crews      = Schema::hasTable('crews')
            ? DB::table('crews')->where('user_id', $id)->get()
            : collect();

        $user->equipments = Schema::hasTable('equipments')
            ? DB::table('equipments')->where('user_id', $id)->get()
            : collect();

        $user->projects   = Schema::hasTable('projects')
            ? DB::table('projects')->where('user_id', $id)->get()
            : collect();

        $embed = request()->boolean('embed');

        // Si tu blade usa variables sueltas (ej. $contacts), pásalas también:
        $contacts   = $user->contacts;
        $crews      = $user->crews;
        $equipments = $user->equipments;
        $projects   = $user->projects;

        return view('admin.users.details', compact('user', 'contacts', 'crews', 'equipments', 'projects'));
    }

}

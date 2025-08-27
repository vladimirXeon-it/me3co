<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\Notification;

if (!function_exists("email_exists")) {
    function email_exists($email)
    {
        $users = DB::table("users")->where("email", $email)->get()->count();
        return $users;
    }
}

if (!function_exists("username_exists")) {
    function username_exists($username)
    {
        $users = DB::table("users")->where("username", $username)->get()->count();
        return $users;
    }
}

if (!function_exists('get_user_role')) {
    function get_user_role($num)
    {
        $roles = [
            'General',
            'Admin'
        ];

        return $roles[$num];
    }
}

if (!function_exists("get_user_details")) {
    function get_user_details()
    {
        return Auth::user();
    }
}
if (!function_exists("generate_uid")) {
    function generate_uid($prefix)
    {
        return $prefix.rand(1000, 9999);
    }
}
if (!function_exists('get_labor_class')) {
    function get_labor_class()
    {
        return DB::table('labor_classes')->get();
    }
}
if (!function_exists('get_user_equipments')) {
    function get_user_equipments()
    {
        return DB::table('equipment')->where('user_id', Auth::id())->get();
    }
}
if (!function_exists('get_equipments')) {
    function get_equipments()
    {
        return DB::table('equipment')->get();
    }
}
if (!function_exists('get_labor_type_name')) {
    function get_labor_type_name($id)
    {
        return DB::table('labor_types')->find($id)->name;
    }
}
if (!function_exists('get_equipment_name')) {
    function get_equipment_name($equipmen_ids)
    {
        $result = '';
        if(!$equipmen_ids) return '';
        foreach (json_decode($equipmen_ids) as $equipmen_id) {
            $name = DB::table('equipment')->find(intval($equipmen_id))->name;
            $result .= "<span class='badge rounded-pill bg-primary mx-1'>$name</span>";
        }

        return $result;
    }
}
if (!function_exists('eq_name')) {
    function eq_name($equipmen_ids)
    {
        $result = '';
        if(!json_decode($equipmen_ids)) return '';
        foreach (json_decode($equipmen_ids) as $equipmen_id) {
            $name = DB::table('equipment')->find(intval($equipmen_id))->name;
            $result .= "$name, ";
        }

        return $result;
    }
}
if (!function_exists('delete_related_data')) {
    function delete_related_data($field_value, $column, $field_name, $table)
    {
        $datas = DB::table($table)->get();
        foreach ($datas as $data) {
            $values = json_decode($data->{$column});
            foreach ($values as $value) {
                if ($value->{$field_name} == $field_value) {
                    DB::table($table)->where('id', $data->id)->delete();
                }
            }
        }
    }
}
if (!function_exists('delete_equipment_data')) {
    function delete_equipment_data($field_value, $column, $table)
    {
        $datas = DB::table($table)->get();
        foreach ($datas as $data) {
            $values = json_decode($data->{$column});
            foreach ($values as $value) {
                if (intval($field_value) === intval($value)) {
                    DB::table($table)->where('id', $data->id)->delete();
                }
            }
        }
    }
}

if (!function_exists('get_user_seetings')) {
    function get_user_settings($id = '')
    {
        $user_id = $id == ''? Auth::id() : $id;
        if (DB::table('settings')->where('user_id', $user_id)->get()->count() == 0) {
            Setting::create([
                'user_id'=>$user_id,
                'auto_update' => 0,
                'location'=>0,
                'status' => 0,
                'notification' =>0,
                'measurement_system' => 0

            ]);
        }
        $settings = Setting::where('user_id', $user_id)->first();

        return $settings;
    }
}


if (!function_exists('get_labor_names')) {
    function get_labor_names()
    {
        $labors = DB::table('labors');
        $idProject = session('idProject');
        if($idProject)
        {
             
            $labors =$labors ->where('user_id',  Auth::id())->where('project_id',  $idProject);
        }
        return $labors->get();
    }
}

if (!function_exists('get_user_labors')) {
    function get_user_labors()
    {
        $labors = DB::table('labors')->where('user_id', Auth::id())->get();
        $idProject = session('idProject');
        if($idProject)
        {
             
            $labors = DB::table('labors')->where('user_id',  Auth::id())->where('project_id',  $idProject)->get();
        }
        return $labors;
    }
}

if (!function_exists('get_material_types')) {
    function get_material_types()
    {
        $material_types = DB::table('material_types')->orderBy('id', 'desc')->get();

        return $material_types;
    }
}
if (!function_exists('get_material_divisions')) {
    function get_material_divisions()
    {
        $material_divisions = DB::table('material_divisions')->orderBy('id', 'desc')->get();

        return $material_divisions;
    }
}

if (!function_exists('get_material_classes')) {
    function get_material_classes()
    {
        $material_classes = DB::table('material_classes')->orderBy('id', 'desc')->get();

        return $material_classes;
    }
}

if (!function_exists('get_materials')) {
    function get_materials()
    {
        $materials = DB::table('materials')->where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return $materials;
    }
}
if (!function_exists('default_materials')) {
    function default_materials()
    {
        $obj = '[]';

        return $obj;
    }
}
if (!function_exists('default_crews')) {
    function default_crews()
    {
        $obj = '[]';

        return $obj;
    }
}
if (!function_exists('default_equipments')) {
    function default_equipments()
    {
        $obj = '[]';

        return $obj;
    }
}
if (!function_exists('get_crews')) {
    function get_crews()
    {
        $crews = DB::table('crews')->where('user_id', Auth::id())->get();

        return $crews;
    }
}
if (!function_exists('get_time_unit')) {
    function get_time_unit($num)
    {
        return ['Month', 'Year'][$num];
    }
}
if (!function_exists('admin_user_id')) {
    function admin_user_id()
    {
        return DB::table('users')->where('role', 1)->first()->id;
    }
}
if (!function_exists('create_notification')) {
    function create_notification($message, $type, $user_id = '')
    {
        Notification::create([
            'user_id' => $user_id == '' ? admin_user_id() : $user_id,
            'type' => $type,
            'message'=> $message
        ]);
    }
}
if (!function_exists('get_user_notifications')) {
    function get_user_notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())->orderBy('id', 'desc')->limit(10)->get();

        return $notifications;
    }
}

if (!function_exists('get_notification_count')) {
    function get_notification_count()
    {
        $number = Notification::where([
            'user_id'=>Auth::id(),
            'status' => 0
        ])->get()->count();

        return $number;
    }
}

if (!function_exists('get_opening_shapes')) {
    function get_opening_shapes()
    {
        $opening_shapes = DB::table('opening_shapes')->orderBy('id', 'desc')->get();

        return $opening_shapes;
    }
}

if (!function_exists('get_projects')) {
    function get_projects()
    {
        $projects = DB::table('projects')->orderBy('id', 'desc')->get();

        return $projects;
    }
}
if (!function_exists('get_user_projects')) {
    function get_user_projects()
    {
        $projects = DB::table('projects')->where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return $projects;
    }
}

if (!function_exists('get_length_units')) {
    function get_length_units()
    {
        $system = Setting::where('user_id', Auth::id())->first()->measurement_system;

        $units = DB::table('units')->where([
            'system' => $system,
            'type' => 0
        ])->first();

        return $units;
    }
}
if(!function_exists('get_user_equipment_unique_id')) {
    function get_user_equipment_unique_id() {
        $userLabors = DB::table('equipment')->select('unique_id')->where('user_id', Auth::id())->where('project_id', null)->get();
        $idProject = session('idProject');
        if($idProject!=null)
        {
            $userLabors = DB::table('equipment')->select('unique_id')->where('user_id', Auth::id())->where('project_id', $idProject)->get();
      
        }
        $arr = [];

        foreach($userLabors as $labor) {
            $arr[] = $labor->unique_id;
        }

        return $arr;
    }
}
if (!function_exists('get_master_equipments')) {
    function get_master_equipments()
    {
        $equipments = DB::table('equipment')->where('user_id', 0)->whereNotIn('unique_id', get_user_equipment_unique_id())->get();
        $idProject = session('idProject');
        if($idProject)
        {
             
            $equipments = DB::table('equipment')->where('user_id',  Auth::id())->where('project_id',  null)->whereNotIn('unique_id', get_user_equipment_unique_id())->get();
        }
        return $equipments;
    }
}
if(!function_exists('get_user_labor_unique_id')) {
    function get_user_labor_unique_id() {
        $userLabors = 
        DB::table('labors')
        ->select('unique_id')
        ->where('user_id', Auth::id())
        ->where('project_id', null)
        ->get();
        $idProject = session('idProject');
        if($idProject!=null)
        {
            $userLabors = DB::table('labors')
            ->select('unique_id')
            
            ->where('project_id', $idProject)
            ->get();
        }

        $arr = [];

        foreach($userLabors as $labor) {
            $arr[] = $labor->unique_id;
        }

        return $arr;
    }
}
if (!function_exists('get_master_labors')) {
    function get_master_labors()
    {
       

         
        $labors = DB::table('labors')->where('user_id', 0)->whereNotIn('unique_id', get_user_labor_unique_id())->get();
        $idProject = session('idProject');
        if($idProject)
        {
            
            $labors = DB::table('labors')->where('user_id',  Auth::id())->where('project_id',  null)->whereNotIn('unique_id', get_user_labor_unique_id())->get();
        }

        return $labors;
    }
}




if(!function_exists('get_user_crews_unique_id')) {
    function get_user_crews_unique_id() {
        $userLabors = 
        DB::table('crews')
        ->select('name')
        ->where('user_id', Auth::id())
        ->where('project_id', null)
        ->get();
        $idProject = session('idProject');
        if($idProject!=null)
        {
            $userLabors = DB::table('crews')
            ->select('name')
            
            ->where('project_id', $idProject)
            ->get();
        }

        $arr = [];

        foreach($userLabors as $labor) {
            $arr[] = $labor->name;
        }

        return $arr;
    }
}
if (!function_exists('get_master_crews')) {
    function get_master_crews()
    {
       

         
        $labors = DB::table('crews')->where('user_id', 0)->whereNotIn('name', get_user_crews_unique_id())->get();
        $idProject = session('idProject');
        if($idProject)
        {
            
            $labors = DB::table('crews')->where('user_id',  Auth::id())->where('project_id',  null)->whereNotIn('name', get_user_crews_unique_id())->get();
        }

        return $labors;

    }
}






if(!function_exists('get_user_materials_unique_id')) {
    function get_user_materials_unique_id() {
        $userMaterials = DB::table('materials')->select('unique_id')->where('user_id', Auth::id())->where('project_id', null)->get();
        $idProject = session('idProject');
        if($idProject!=null)
        {
            $userMaterials = DB::table('materials')->select('unique_id')->where('user_id', Auth::id())->where('project_id', $idProject)->get();
      
        }

        $arr = [];

        foreach($userMaterials as $material) {
            $arr[] = $material->unique_id;
        }

        return $arr;
    }
}
if (!function_exists('get_master_materials')) {
    function get_master_materials()
    {
        $materials = DB::table('materials')->where('user_id', 0)->whereNotIn('unique_id', get_user_materials_unique_id())->get();
        $idProject = session('idProject');
        if($idProject)
        {
            
            $materials = DB::table('materials')->where('user_id',  Auth::id())->where('project_id',  null)->whereNotIn('unique_id', get_user_materials_unique_id())->get();
        }
        return $materials;
    }
}
if (!function_exists('static_pages')) {
    function static_pages() 
    {
        $pages = DB::table('static_pages')->get();

        return $pages;
    }
}
if (!function_exists('get_default_units')) {
    function get_default_units()
    {
        $units = DB::table('default_units')->orderBy('id', 'desc')->get();
        return $units;
    }
}
if (!function_exists('generate_material_id')) {
    function generate_material_id($material_class_id)
    {
        $material_class_name = DB::table('material_classes')->find($material_class_id)->name;

        $prefix = substr($material_class_name, 0, 3);
        return strtoupper($prefix).rand(1000, 9999);
    }
}
if (!function_exists('gmp_gcd')) {
    function gmp_gcd($x, $y)
    {
        if ($y == 0) {
            return $x;
        }
        return gmp_gcd($y, $x%$y);
    }
}
if (!function_exists('generate_unique_dir')) {
    function generate_unique_dir()
    {
        $dir = 'project'.rand(100000, 999999);
        if (DB::table('documents')->where('directory', $dir)->first() !== null) {
            $dir = generate_unique_dir();
        }
        return $dir;
    }
}
if (!function_exists('create_folder_tree')) {
    function create_folder_tree($input, $folder)
    {
        $result = array();

        foreach ($input as $path) {
            $prev = &$result;
            $newPath = str_replace($folder, '', $path);
            $s = strtok($newPath, '/');

            while (($next = strtok('/')) !== false) {
                if (!isset($prev[$s])) {
                    $prev[$s] = array();
                }

                $prev = &$prev[$s];
                $s = $next;
            }

            $prev[$s] = $path;

            unset($prev);
        }

        return $result;
    }
}
if (!function_exists('base64_url_encode')) {
    function base64_url_encode($input) {
        return strtr(base64_encode($input), '+/=', '._-');
    }
}
if(!function_exists('base64_url_decode')) {
    function base64_url_decode($input) {
        return base64_decode(strtr($input, '._-', '+/='));
    }    
}
if(!function_exists('get_user_contacts')) {
    function get_user_contacts() {
        $contacts = DB::table('contacts')->where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return $contacts;
    }
}

if(!function_exists('get_subscriptions')) {
    function get_subscriptions() {
        $subs = DB::table('plans')->get();

        return $subs;
    }
}

if(!function_exists('get_plan_feature')) {
    function get_plan_feature($str) {
        $trim_str = ltrim($str, "-");
        $str_arr = explode("-", $trim_str);

        return $str_arr;
    }
}

if(!function_exists('get_static_pages')) {

    function get_static_pages() {
        $pages = DB::table('static_pages')->get();

        return $pages;
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Equipment;
use App\Models\LaborClass;
use App\Models\LaborType;
use App\Models\Labor;
use App\Models\Crew;
use App\Models\Contact;
use App\Models\Company;
use App\Models\Setting;
use App\Models\Opening;
use App\Models\MaterialDivision;
use App\Models\MaterialClass;
use App\Models\Material;
use App\Models\Plan;
use App\Models\OpeningShape;
use App\Models\DefaultUnit;
use App\Models\StaticPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Hash;
class AdminController extends Controller
{
    public function __construct() {
        $this->middleware(function ($request, $next) {
            if(!Auth::check() || Auth::user()->role !== 1) {
                return \redirect()->to("admin/login");
            }
            return $next($request);
        })->except(["login", "admin_login"]);
    }

    public function login() {
        return view("admin.auth.login");
    }
    public function logout() {
        Auth::logout();

        return redirect()->to("/admin/login");
    }
    public function admin_login(Request $request) {
        $cradentials = $request->validate([
            "username" => "required",
            "password"=> "required"
        ]);
        $remember_me = $request->has('remember_me') ? true : false; 
        if(Auth::attempt($cradentials, $remember_me) && Auth::user()->role == 1){
            return redirect()->to("/admin");
        }

        return redirect()->back()->with("message", "Wrong Credentials!");
    }

    public function index() {
        return view("admin.index");
    }

    public function equipments() {
        $equipments = Equipment::where('user_id', 0)->get();
        return view("admin.equipments.index", compact("equipments"));
    }
    public function add_equipments() {
        return view('admin.equipments.add');
    }

    public function create_equipments(Request $request) {
        $isSuccess = Equipment::create([
            'user_id' => 0,
            'name' => $request->post('name'),
            'description' => $request->post('description'),
            'cost_per_day' => $request->post('cost_per_day'),
            'unique_id' => generate_uid('EQUIP')
        ]);

        if($isSuccess) {
            return redirect()->route('admin.equipments')->with('message', '&check Created a new equipment');
        }
        return back()->with('message', '⚠ Error while creating Equipment');
    }
    public function delete_equipments($id) {
        delete_equipment_data($id, "equipment_ids", "crews");
        $isSuccess = Equipment::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Equipment Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_equipments($id) {
        $equipment = Equipment::find($id);

        return view('admin.equipments.edit', compact('equipment'));
    }

    public function update_equipments(Request $request, $id) {
        $isSuccess = Equipment::find($id)->update([
            "name" => $request->post("name"),
            "cost_per_day" => $request->post("cost_per_day")
        ]);

        if($isSuccess) {
            return redirect()->to('admin/equipments')->with("message", "&check; Equipment Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }
    
    public function labor_class() {
        $labor_classes = LaborClass::get();
        return view("admin.labors.class", compact('labor_classes'));
    }

    public function create_labor_class(Request $request) {
        $isSuccess = LaborClass::create([
            "name" => $request->post("name"),
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; New Labor Class Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
    }
    public function update_labor_class(Request $request, $id) {
        $isSuccess = LaborClass::find($id)->create([
            "name" => $request->post("name"),
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }

    public function delete_labor_class($id) {
        $isSuccess = LaborClass::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function labor() {

        $labors = Labor::where('user_id', 0)->orderBy('id','desc')->get();

        return view("admin.labors.index", compact("labors"));
    }
    public function add_labor() {
        return view('admin.labors.add');
    }
    public function create_labor(Request $request) {
        $isSuccess = Labor::create([
            "user_id" => 0,
            "labor_class_id" => $request->post("labor_class_id"),
            "unique_id" => generate_uid("LAB"),
            "labor_type" => $request->post("labor_type"),
            "cost_per_hour" => $request->post("cost_per_hour"),
            "burdens" => json_encode($request->burdens),
            "total_cost" => $request->post("total_cost"),
        ]);

        if($isSuccess) {
            return redirect()->to('admin/labor')->with("message", "&check; Labor Created!");
        }

        return back()->with("message", "⚠ Error While creating Record!");
    }
    public function edit_labor($id) {
        $labor = Labor::find($id);

        return view('admin.labors.edit', compact('labor'));
    }
    public function delete_labor($id) {
        $isSuccess = Labor::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }
    public function update_labor(Request $request, $id) {
        $isSuccess = Labor::find($id)->update([
            "labor_class_id" => $request->post("labor_class_id"),
            "labor_type" => $request->post("labor_type"),
            "cost_per_hour" => $request->post("cost_per_hour"),
            "burdens" => json_encode($request->burdens),
            "total_cost" => $request->post("total_cost"),
        ]);

        if($isSuccess) {
            return redirect()->to('admin/labor')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }


    public function crews() {
        $crews = Crew::get();

        return view('admin.crews.index', compact('crews'));
    }

    public function update_crews(Request $request, $id) {
        $isSuccess = Crew::find($id)->update([
            'name' => $request->post('name'),
            'description' => $request->post('description'),
            'equipment_ids' => json_encode($request->post('equipment_ids')),
            'labor_info' => json_encode($request->post('labor_info'))
        ]);

        if($isSuccess) {
            return redirect()->to('admin/crews')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");

    }

    public function delete_crews($id) {
        $isSuccess = Crew::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_crews($id) {
        $crew = Crew::find($id);

        return view('admin.crews.edit', compact('crew'));
    }

    public function contacts() {
        $contacts = Contact::orderBy('id', 'desc')->get();

        return view('admin.contacts.index', compact('contacts'));
    }
    public function delete_contacts($id) {
        $isSuccess = Contact::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_contacts($id) {
        $contact = Contact::find($id);

        return view('admin.contacts.edit', compact('contact'));
        
    }

    public function update_contacts(Request $request, $id) {
        $isSuccess = Contact::find($id)->update([
            'name' => $request->post('name'),
            'company' => $request->post('company'),
            'phone' => $request->post('phone'),
            'email' => $request->post('email'),
            'address' => $request->post('address'),
            'city' => $request->post('city'),
            'state' => $request->post('state'),
            'country' => $request->post('country'),
            'zip' => $request->post('zip'),
            
        ]);

        if($isSuccess) {
            return redirect()->to("admin/contacts")->with("message", "&check; Record updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }

    public function company() {
        $companies = Company::orderBy('id', 'desc')->get();

        return view('admin.company.index', compact('companies'));
    }

    public function delete_company($id) {
        $isSuccess = Company::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_company($id) {
        $company = Company::find($id);

        return view('admin.company.edit', compact('company'));
        
    }

    public function update_company(Request $request, $id) {
        $isSuccess = Company::find($id)->update([
            'name' => $request->post('name'),
            'estimator' => $request->post('estimator'),
            'phone' => $request->post('phone'),
            'email' => $request->post('email'),
            'address' => $request->post('address'),
            'city' => $request->post('city'),
            'state' => $request->post('state'),
            'country' => $request->post('country'),
            'zip' => $request->post('zip'),
            
        ]);

        if($isSuccess) {
            return redirect()->to("admin/company")->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }


    public function setting_update(Request $request) {
        $user_id = Auth::id();
        $isSuccess = Setting::where('user_id', $user_id)->update([
            'auto_update' => $request->has('auto_update')?1:0,
            'location' => $request->has('location')?1:0,
            'status' => $request->has('status')?1:0,
            'notification' => $request->has('notification')?1:0,
        ]);


        return $isSuccess;
    }

    public function change() {
        return view('admin.auth.change');
    }

    public function change_password(Request $request) {
        $isSuccess = User::find(Auth::id())->update([
            'username' => $request->post('username'),
            'password' => Hash::make($request->post('password')),
        ]);

        if($isSuccess) {
            return redirect()->to('/admin')->with("message", "&check; Credentials Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Credentials!");
    }

    public function opening() {
        $openings = Opening::orderBy('id', 'desc')->get();
        return view('admin.openings.index', compact('openings'));
    }
    public function delete_opening($id) {
        $isSuccess = Opening::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");

    }

    public function edit_opening($id) {
        $opening = Opening::find($id);

        return view('admin.openings.edit', compact('opening'));
    }
    
    public function update_opening(Request $request, $id) {
        $isSuccess = Opening::find($id)->update([
            'project_id' => $request->post('project_id'),
            'labor_class_id' => $request->post('labor_class_id'),
            'labor_type_id' => $request->post('labor_type_id'),
            'opening_shape_id' => $request->post('opening_shape_id'),
            'description' => $request->post('description'),
            'measurement_unit' => $request->post('measurement_unit'),
            'length' => $request->post('length'),
            'height' => $request->post('height'),
            'elevation' => $request->post('elevation'),
            'header' => $request->post('header'),
            'bearing' => $request->post('bearing'),
            'materials' => json_encode($request->post('materials')),
            'caulking' => json_encode($request->post('caulking'))
        ]);

        if($isSuccess) {
            return redirect()->to('admin/opening')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }
    
   

    public function material_division() {
        $material_divisions = MaterialDivision::orderBy('id', 'desc')->get();

        return view('admin.material.division', compact('material_divisions'));
    }

    public function material_division_create(Request $request) {
        $isSuccess = MaterialDivision::create([
            'name' => $request->post('name')
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
    }
    public function material_division_update(Request $request, $id) {
        $isSuccess = MaterialDivision::find($id)->create([
            'name' => $request->post('name')
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While updating Record!");
    }


    public function material_division_delete($id) {
        $isSuccess = MaterialDivision::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function material_class() {
        $material_classes = MaterialClass::orderBy('id', 'desc')->get();

        return view('admin.material.class', compact('material_classes'));
    }

    public function material_class_create(Request $request) {
        $isSuccess = MaterialClass::create([
            'material_division_id' => $request->post('material_division_id'),
            'name' => $request->post('name')
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
    }
    public function material_class_update(Request $request, $id) {
        $isSuccess = MaterialClass::find($id)->update([
            'material_division_id' => $request->post('material_division_id'),
            'name' => $request->post('name')
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }


    public function material_class_delete($id) {
        $isSuccess = MaterialClass::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function material() {
        $materials = Material::where('user_id', 0)->orderBy('id','desc')->get();

        return view('admin.material.index', compact('materials'));
    }
    public function create_material(Request $request) {
        $isSuccess = Material::create([
            'user_id' => 0,
            'name' => $request->post('name'),
            'material_class_id' => $request->post('material_class_id'),
            'material_division_id' => $request->post('material_division_id'),
            'description' => $request->post('description'),
            'measurement_unit' => $request->post('measurement_unit'),
            'default_unit' => $request->post('default_unit'),
            'unique_id' => \generate_material_id($request->post('material_class_id')),
            'height' => $request->post('height'),
            'width' => $request->post('width'),
            'length' => $request->post('length'),
            'waste' => $request->post('waste'),
            'prices' => $request->post('prices'),
            'production_rate' => $request->post('production_rate'),
            'production_subed_out_cost' => $request->post('production_subed_out_cost'),
            'cleaning_cost' => $request->post('cleaning_cost'),
            'cleaning_subed_out' => $request->post('cleaning_subed_out'),
            'associated_products' => json_encode($request->post('associated_products'))
        ]);

        if($isSuccess) {
            return redirect()->to('admin/material')->with("message", "&check; Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
    }
    public function delete_material($id) {
        $isSuccess = Material::find($id)->delete();
        if($isSuccess) {
            return back()->with("message", "&check; Record Deleled!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");

    }
    public function get_material_class($id) {
        $material_class = MaterialClass::where('material_division_id', $id)->get();

        return \json_decode($material_class);
    }
    public function add_material() {
        return view('admin.material.add');
    }
    public function edit_material($id) {
        $material = Material::find($id);

        return view("admin.material.edit", compact('material'));
    }

    public function update_material(Request $request, $id) {
        $isSuccess = Material::find($id)->update([
            'material_class_id' => $request->post('material_class_id'),
            'material_division_id' => $request->post('material_division_id'),
            'description' => $request->post('description'),
            'measurement_unit' => $request->post('measurement_unit'),
            'default_unit' => $request->post('default_unit'),
            'height' => $request->post('height'),
            'width' => $request->post('width'),
            'length' => $request->post('length'),
            'waste' => $request->post('waste'),
            'prices' => $request->post('prices'),
            'production_rate' => $request->post('production_rate'),
            'production_subed_out_cost' => $request->post('production_subed_out_cost'),
            'cleaning_cost' => $request->post('cleaning_cost'),
            'cleaning_subed_out' => $request->post('cleaning_subed_out'),
            'associated_products' => json_encode($request->post('associated_products'))
        ]);

        if($isSuccess) {
            return redirect()->to('admin/material')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }

    public function user() {
        $users = User::where('id','!=', Auth::id())->orderBy('id', 'desc')->get();

        return view('admin.users.index', compact('users'));
    }

    public function get_user($id) {
        $user = User::find($id);

        return view('admin.users.details', compact('user'));
    }

    public function update_user(Request $request, $id) {
        $isSuccess = User::find($id)->update([
            'name' => $request->post('name'),
            'email' => $request->post('email'),
            'username' => $request->post('username'),
            'company' => $request->post('company'),
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; User Details Updated!");
        }

        return back()->with("message", "⚠ Error While Updating User Details!");
    }
    public function update_user_settings(Request $request, $id) {
        $isSuccess = Setting::where('user_id', $id)->update([
            'auto_update' => $request->has('auto_update')?1:0,
            'location' => $request->has('location')?1:0,
            'status' => $request->has('status')?1:0,
            'notification' => $request->has('notification')?1:0,
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; User Setings Updated!");
        }

        return back()->with("message", "⚠ Error While Updating User Settings!");
    }

    public function subscriptions() {
        $plans = Plan::orderBy('id', 'desc')->get();

        return view('admin.subscriptions.index', compact('plans'));
    }

    public function create_subscriptions(Request $request) {
        $isSuccess = Plan::create([
            'name' => $request->post('name'),
            'type' => $request->post('type'),
            'price' => $request->post('price'),
            'time_unit' => $request->post('time_unit'),
            'project_allowed' => $request->post('project_allowed'),
            'description' => $request->post('description'),
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; New Subscription Plan Created!");
        }

        return back()->with("message", "⚠ Error While Creating Subscription Plan!");

        
    }

    public function delete_subscriptions($id) {
        $isSuccess = Plan::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Subscription Plan Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Subscription Plan!");
    }

    public function edit_subscriptions($id) {
        $plan = Plan::find($id);

        return view('admin.subscriptions.edit', compact('plan'));
    }

    public function update_subscriptions(Request $request, $id) {
        $isSuccess = Plan::find($id)->update([
            'name' => $request->post('name'),
            'type' => $request->post('type'),
            'price' => $request->post('price'),
            'time_unit' => $request->post('time_unit'),
            'project_allowed' => $request->post('project_allowed'),
            'description' => $request->post('description'),
        ]);

        if($isSuccess) {
            return redirect()->to('admin/subscriptions')->with("message", "&check; Subscription Plan Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Subscription Plan!");
    }

    public function opening_shape() {
        $openingShapes = OpeningShape::orderBy('id', 'desc')->get();

        return view('admin.openings.shape',compact('openingShapes') );
    }

    public function create_opening_shape(Request $request) {
        $isSuccess = OpeningShape::create([
            'name' => $request->post('name')
        ]);

        if($isSuccess) {
            return back()->with('message', '&check; A New Opening Shape Created!');
        }
        return back()->with('message', '&times; Error while creating aNew Opening Shape!');
    }

    public function delete_opening_shape($id) {
        $isSuccess = OpeningShape::find($id)->delete();

        if($isSuccess){
            return back()->with('message', '&check; Opening Shape Deleted!');
        }

        return back()->with('message', '&times; Error while deleting Opening Shape');
    }

    public function default_units() {
        $default_units = DefaultUnit::orderBy('id', 'desc')->get();

        return view('admin.material.unit', compact('default_units'));
    }
    public function update_default_unit(Request $request, $id) {
        $isSuccess = DefaultUnit::find($id)->update([
            'unit' => $request->post('unit')
        ]);

        if($isSuccess) {
            return back()->with('message', '&check; Record Updated!');
        }

        return back()->with('message', '&times; Error while updating record');
    }
    public function create_default_unit(Request $request) {
        $isSuccess = DefaultUnit::create([
            'unit' => $request->post('unit')
        ]);

        if($isSuccess) {
            return back()->with('message', '&check; A new default unit created!');
        }

        return back()->with('message', '&times; Error while creating record');
    }

    public function delete_default_unit($id) {
        $isSuccess = DefaultUnit::find($id)->delete();

        if($isSuccess) {
            return back()->with('message', '&check; A Record Deleted!');
        }

        return back()->with('message', '&times; Error while creating record');
    }

    public function add_static(Request $request) {
        return view('admin.static.add');
    }
    public function edit_static(Request $request, $id) {
        $page = StaticPage::find($id);
        return view('admin.static.edit', compact('page'));
    }

    public function create_static(Request $request) {
        $title = $request->post('title');
        $content = $request->post('content');
        $slug = Str::slug($title);

        $isSuccess = StaticPage::create([
            'title' => $title,
            'content' => $content,
            'slug' => $slug
        ]);

        if($isSuccess) {
            return back()->with('message', '&check; A new page created');
        }
        return back()->with('message', '&times; Error occured');
    }

    public function update_static(Request $request, $id) {
        $title = $request->post('title');
        $content = $request->post('content');
        $slug = Str::slug($title);

        $isSuccess = StaticPage::find($id)->update([
            'title' => $title,
            'content' => $content,
            'slug' => $slug
        ]);

        if($isSuccess) {
            return back()->with('message', '&check; Updated');
        }
        return back()->with('message', '&times; Error occured');
    }
    public function delete_static($id) {
        $isSuccess = StaticPage::find($id)->delete();
        if($isSuccess) {
            return redirect()->route('admin.static.add')->with('message', '&check; Deleted');
        }
        return redirect()->route('admin.static.add')->with('message', '&times; Error occured');
    }
}

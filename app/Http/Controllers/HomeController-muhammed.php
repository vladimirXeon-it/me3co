<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\User;

use App\Models\Labor;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Opening;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Material;
use App\Models\Equipment;
use Illuminate\Http\Request;
use App\Models\InviteProject;
use App\Models\MaterialClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    
    public function measurement(Request $request, $id) {
        if(auth()->user()){
            return view('user.measurement', compact('id'));
        }else{
            return view('user.auth.login');
        }
    }
    public function startMeasurement(Request $request, $id) {
        // return view('user.measurement', compact('id'));
        
        if(auth()->user()){
             $appUrl = env('APP_URL');
             $targetUrl = "{$appUrl}/{$id}/application";
             // Redirect to the target URL
             return Redirect::away($targetUrl);
            // return view('user.measurement', compact('id'));
        }else{
            return view('user.auth.login');
        }
    }
    public function project() {
        $projects = Project::where('user_id', Auth::id())
                    ->orderBy('id', 'desc')
                    ->get();
        $userEmail = User::where('id', Auth::id())->first()->email;
        $inviteIds = InviteProject::where('invite_to', $userEmail)->where('status', '1')->pluck('project_id')->toArray();
        $inviteProjects = Project::whereIn('id',  $inviteIds)->get();
        return view('user.projects.index', compact('projects', 'inviteProjects'));
    }
    public function create_project_page() {
        return view("user.projects.create");
    }
    public function create_project(Request $request) {
        
        $isSuccess = Project::create([
            'user_id' => Auth::id(),
            'bid_number' => \generate_uid('BID'),
            'name' => $request->post('name'),
            'estimator' => $request->post('estimator'),
            'address' => $request->post('address'),
            'city' => $request->post('city'),
            'state' => $request->post('state'),
            'zip' => $request->post('zip'),
            'weather_adjustment' => $request->post('weather_adjustment'),
            'country' => $request->post('country'),
            'customer' => $request->post('customer'),
            'bid_date' => $request->post('bid_date'),
            'bid_time' => $request->post('bid_time'),
            'items' => json_encode($request->post('items')),
            'name' => $request->post('name'),
            'other_equipment' => $request->post('other_equipment'),
            'other_labor' => $request->post('other_labor'),
            'material_profit_info' => json_encode($request->post('material_profit_info')),
            'labor_profit_info' => json_encode($request->post('labor_profit_info')),
            'equipment_profit_info' => json_encode($request->post('equipment_profit_info')),
            'subcontractor_profit_info' => json_encode($request->post('subcontractor_profit_info')),
            'other_profit_info' => json_encode($request->post('other_profit_info')),
            'materials' => default_materials(),
            'crews' => default_crews(),
            'equipments' => default_equipments()
        ]);

        if($isSuccess) {
            create_notification(Auth::user()->name." Created a New Project", "success");
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");

    }
    function createNewProject(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'bid_date' => 'required|date',
        ]);
        $user_id = Auth::id();
        $name = $validatedData['name'];
        $project = new Project();
        $project->name = $name;
        $project->bid_date = $validatedData['bid_date'];
        $project->user_id = $user_id;
        $project->save();
        return back()->with("message", "Project Created Successfully!");
    }
    function editNewProject($id) {
        $project = Project::find($id);
        return response()->json([
            'status' => 200,
            'project' => $project,
        ]);
    }
    function updateNewProject(Request $request) {
        $projectId = $request->project_id;
        $project = Project::find($projectId);
        $project->name = $request->input('name');
        $project->bid_date = $request->input('bid_date');
        $project->update();
        return back()->with("message", "Project Updated Successfully!");
    }
    public function update_project(Request $request, $id) {
        
        $isSuccess = Project::find($id)->update([
            'name' => $request->post('name'),
            'estimator' => $request->post('estimator'),
            'address' => $request->post('address'),
            'city' => $request->post('city'),
            'state' => $request->post('state'),
            'zip' => $request->post('zip'),
            'country' => $request->post('country'),
            'customer' => $request->post('customer'),
            'bid_date' => $request->post('bid_date'),
            'bid_time' => $request->post('bid_time'),
            'items' => json_encode($request->post('items')),
            'name' => $request->post('name'),
            'weather_adjustment' => $request->post('weather_adjustment'),
            'other_equipment' => $request->post('other_equipment'),
            'other_labor' => $request->post('other_labor'),
            'material_profit_info' => json_encode($request->post('material_profit_info')),
            'labor_profit_info' => json_encode($request->post('labor_profit_info')),
            'equipment_profit_info' => json_encode($request->post('equipment_profit_info')),
            'subcontractor_profit_info' => json_encode($request->post('subcontractor_profit_info')),
            'other_profit_info' => json_encode($request->post('other_profit_info')),
            'materials' => json_encode($request->post('materials')),
            'crews' => json_encode($request->post('crews')),
            'equipments' => json_encode($request->post('equipments'))
        ]);

        if($isSuccess) {
            return redirect()->route('project')->with("message", "&check; Record Updated Successfully");
        }

        return back()->with("message", "⚠ Error While Updating Record!");

    }

    public function delete_project($id) {
        $isSuccess = Project::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted Successfully!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_project($id) {
        $project = Project::find($id);

        return view('user.projects.edit', compact('project'));
    }
    public function labor() {
        $user_id = Auth::id();
        $labors = Labor::where('user_id', $user_id)->orderBy('id', 'desc')->get();

        return view('user.labors.index', compact('labors'));
    }

    public function create_labor_page() {
        return view("user.labors.create");
    }

    public function import_labor($id) {
        $labor = Labor::find($id);

        $isSuccess = Labor::create([
            "user_id" => Auth::id(),
            "labor_class_id" => $labor->labor_class_id,
            "labor_type" => $labor->labor_type,
            "unique_id" => $labor->unique_id,
            "cost_per_hour" => $labor->cost_per_hour,
            "burdens" => $labor->burdens,
            "total_cost" => $labor->total_cost,
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; Labor Imported!");
        }

        return back()->with("message", "⚠ Error While Importing Record!");

    }
    public function create_labor(Request $request) {
        $isSuccess = Labor::create([
            "user_id" => Auth::id(),
            "labor_class_id" => $request->post("labor_class_id"),
            "labor_type" => $request->post("labor_type"),
            "unique_id" => \generate_uid("LAB"),
            "cost_per_hour" => $request->post("cost_per_hour"),
            "burdens" => json_encode($request->burdens),
            "total_cost" => $request->post("total_cost"),
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
    }

    public function edit_labor($id) {
        $labor = Labor::find($id);

        return view('user.labors.edit', compact('labor'));
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
            return redirect()->to('labor')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }


    public function crew() {
        $crews = Crew::where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return view('user.crews.index', compact('crews'));
    }

    public function create_crew_page() {
        return view("user.crews.create");
    }

    public function create_crew(Request $request) {
        $isSuccess = Crew::create([
            'user_id' => Auth::id(),
            'name' => $request->post('name'),
            'description' => $request->post('description'),
            'labor_info' => json_encode($request->post('labor_info'))
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");

    }
    public function update_crew(Request $request, $id) {
        $isSuccess = Crew::find($id)->update([
            'name' => $request->post('name'),
            'description' => $request->post('description'),
            'labor_info' => json_encode($request->post('labor_info'))
        ]);

        if($isSuccess) {
            return redirect()->to('crew')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");

    }

    public function delete_crew($id) {
        $isSuccess = Crew::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_crew($id) {
        $crew = Crew::find($id);

        return view('user.crews.edit', compact('crew'));
    }

    public function equipment() {
        $equipments = Equipment::where('user_id', Auth::id())->orderBy("id", "desc")->get();

        return view('user.equipments.index', compact('equipments'));
    }
    public function import_equipment($id) {
        $equipment = Equipment::find($id);

        $isSuccess = Equipment::create([
            'user_id' => Auth::id(),
            'name' => $equipment->name,
            'description' => $equipment->description,
            'unique_id' => $equipment->unique_id,
            'cost_per_day' => $equipment->cost_per_day
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; Equipment Imported!");
        }

        return back()->with("message", "⚠ Error While Importing Equipment!");

    }
    public function create_equipment_page() {
        return view("user.equipments.create");
    }
    public function create_equipment(Request $request) {
        $isSuccess = Equipment::create([
            "user_id" => Auth::id(),
            "name" => $request->post("name"),
            "description" => $request->post('description'),
            "unique_id" => \generate_uid("EQIP"),
            "cost_per_day" => $request->post("cost_per_day")
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; New Equipment Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
    }

    public function delete_equipment($id) {
        // delete_equipment_data($id, "equipment_ids", "crews");
        $isSuccess = Equipment::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Equipment Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_equipment($id) {
        $equipment = Equipment::find($id);

        return view('user.equipments.edit', compact('equipment'));
    }

    public function update_equipment(Request $request, $id) {
        $isSuccess = Equipment::find($id)->update([
            "name" => $request->post("name"),
            "description" => $request->post('description'),
            "cost_per_day" => $request->post("cost_per_day")
        ]);

        if($isSuccess) {
            return redirect()->to('equipment')->with("message", "&check; Equipment Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }

    public function contact() {
        $contacts = Contact::where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return view('user.contacts.index', compact('contacts'));
    }
    public function create_contact_page() {
        return view("user.contacts.create");
    }
    public function create_contact(Request $request) {
        $isSuccess = Contact::create([
            'user_id' => Auth::id(),
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
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
    }
    public function add_material() {
        return view('user.materials.add');
    }
    public function delete_contact($id) {
        $isSuccess = Contact::find($id)->delete();

        if($isSuccess) {
            return back()->with("message", "&check; Record Deleted!");
        }

        return back()->with("message", "⚠ Error While Deleting Record!");
    }

    public function edit_contact($id) {
        $contact = Contact::find($id);

        return view('user.contacts.edit', compact('contact'));
        
    }

    public function update_contact(Request $request, $id) {
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
            return redirect()->to("contact")->with("message", "&check; Record updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }

    public function company() {
        $companies = Company::where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return view('user.company.index', compact('companies'));
    }

    public function create_company(Request $request) {
        $isSuccess = Company::create([
            'user_id' => Auth::id(),
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
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
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

        return view('user.company.edit', compact('company'));
        
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
            return redirect()->to("company")->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }

    public function opening() {
        $openings = Opening::where('user_id', Auth::id())->orderBy('id', 'desc')->get();

        return view('user.openings.index', compact('openings'));
    }

    public function create_opening_page() {
        return view("user.openings.create");
    }

    public function create_opening(Request $request) {
        $isSuccess = Opening::create([
            'user_id' => Auth::id(),
            'project_id' => $request->post('project_id'),
            'labor_class_id' => $request->post('labor_class_id'),
            'labor_id' => $request->post('labor_id'),
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
            return back()->with("message", "&check; New Record Created!");
        }

        return back()->with("message", "⚠ Error While Creating Record!");
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

        return view('user.openings.edit', compact('opening'));
    }
    
    public function update_opening(Request $request, $id) {
        $isSuccess = Opening::find($id)->update([
            'project_id' => $request->post('project_id'),
            'labor_class_id' => $request->post('labor_class_id'),
            'labor_id' => $request->post('labor_id'),
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
            return redirect()->to('opening')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }
    public function update_settings(Request $request) {
        $user_id = Auth::id();
        $isSuccess = Setting::where('user_id', $user_id)->update([
            'auto_update' => $request->has('auto_update')?1:0,
            'location' => $request->has('location')?1:0,
            'status' => $request->has('status')?1:0,
            'notification' => $request->has('notification')?1:0,
            'measurement_system' => $request->has('measurement_system')?1:0
        ]);


        return $isSuccess;
    }

    public function get_material_class($id) {
        $material_class = MaterialClass::where('material_division_id', $id)->get();

        return \json_decode($material_class);
    }
    public function material() {
        $materials = Material::where('user_id', Auth::id())->orderBy('id', 'desc')->get();
        return view('user.materials.index', compact('materials'));
    }
    public function import_material($id) {
        $material = Material::find($id);

        $isSuccess = Material::create([
            'user_id' => Auth::id(),
            'name' => $material->name,
            'material_class_id' => $material->material_class_id,
            'material_division_id' => $material->material_division_id,
            'unique_id' => $material->unique_id,
            'default_unit' => $material->default_unit,
            'description' => $material->description,
            'measurement_unit' => $material->measurement_unit,
            'height' => $material->height,
            'width' => $material->width,
            'length' => $material->length,
            'waste' => $material->waste,
            'prices' => $material->prices,
            'production_rate' => $material->production_rate,
            'production_subed_out_cost' => $material->production_subed_out_cost,
            'cleaning_cost' => $material->cleaning_cost,
            'cleaning_subed_out' => $material->cleaning_subed_out,
            'associated_products' => $material->associated_products,
            
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; Material Imported!");
        }

        return back()->with("message", "⚠ Error Importing Material!");

    }
    public function create_material(Request $request) {
        $isSuccess = Material::create([
            'user_id' => Auth::id(),
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
            'subbed_out_rate' => $request->post('subbed_out_rate'),
            'production_subed_out_cost' => $request->post('production_subed_out_cost'),
            'cleaning_cost' => $request->post('cleaning_cost'),
            'cleaning_subed_out' => $request->post('cleaning_subed_out'),
            'associated_products' => json_encode($request->post('associated_products'))
        ]);

        if($isSuccess) {
            return back()->with("message", "&check; Record Created!");
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

    public function edit_material($id) {
        $material = Material::find($id);

        return view("user.materials.edit", compact('material'));
    }

    public function update_material(Request $request, $id) {
        $isSuccess = Material::find($id)->update([
            'material_class_id' => $request->post('material_class_id'),
            'material_division_id' => $request->post('material_division_id'),
            'description' => $request->post('description'),
            'measurement_unit' => $request->post('measurement_unit'),
            'default_unit' => $request->post('default_unit'),
            'name' => $request->post("name"),
            'height' => $request->post('height'),
            'width' => $request->post('width'),
            'length' => $request->post('length'),
            'waste' => $request->post('waste'),
            'prices' => $request->post('prices'),
            'production_rate' => $request->post('production_rate'),
            'subbed_out_rate' => $request->post('subbed_out_rate'),
            'production_subed_out_cost' => $request->post('production_subed_out_cost'),
            'cleaning_cost' => $request->post('cleaning_cost'),
            'cleaning_subed_out' => $request->post('cleaning_subed_out'),
            'associated_products' => json_encode($request->post('associated_products'))
        ]);

        if($isSuccess) {
            return redirect()->to('material')->with("message", "&check; Record Updated!");
        }

        return back()->with("message", "⚠ Error While Updating Record!");
    }

    public function assembly() {
        return view('user.assemblies.index');
    }
}
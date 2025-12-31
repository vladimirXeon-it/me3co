<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Crew;
use App\Models\User;
use App\Models\Labor;
//use CloudConvert\Api;
//use CloudConvert\Job;
//use CloudConvert\Task;
use \CloudConvert\Laravel\Facades\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;




use App\Mail\Proposal;
use App\Models\Project;
use App\Models\Document;
use App\Models\Material;
use App\Models\Equipment;
use App\Jobs\PlanUploadJob;
use App\Mail\PasswordReset;
use App\Casts\MakeEquipment;
use App\Models\LineTemplate;
use Illuminate\Http\Request;
use App\Models\MaterialClass;
use App\Models\MaterialDivision;
use App\Models\Wall;
use App\Http\Controllers\WallController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PdfReader;
use Illuminate\Support\Facades\DB;   
use Illuminate\Support\Facades\Validator;   // si validas
use App\Models\CourseBand;   
use Illuminate\Support\Arr; 
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function sendReport(Request $request, $project_id)
    {
        $file = $request->file('file');
        $tmpdir = \public_path("uploads/tmp");
        $hashname = "project-report-" . rand(10000, 99999) . ".pdf";
        $filename = $file->move($tmpdir, $hashname);
        $tmppath = $tmpdir . '/' . $hashname;

        $project = Project::find($project_id);
        $customerEmail = $project->projectCustomer->email;
        $user = Auth::user();
        try {
            Mail::to($customerEmail)
                ->send(new Proposal($user, $project, $tmppath));

            @unlink($tmppath);
            return [
                'status' => true,
                'message' => 'Email Sent',
                'filename' => $tmppath
            ];
        } catch (\Exception $err) {
            return [
                'status' => false,
                'message' => 'A unknown error. Please try again later',
                'filename' => $tmppath
            ];
        }
    }
    public function sendPasswordResetEmail(Request $request)
    {
        $email = $request->post('email');
        $user = User::where([
            'email' => $email
        ])->first();

        if ($user == null) {
            return [
                'status' => false,
                'message' => "Email address doesn't exit"
            ];
        }

        Mail::to($user)->send(new PasswordReset($user));

        return [
            'status' => true
        ];
    }
    public function generate_project_report(Request $request, $project_id)
    {
        try {
            $project = Project::find($project_id);

            $requestBody = $request->all();

            $countTakeOffs = $requestBody['counts'];
            $perimterTakeOffs = $requestBody['perimeters'];
            $lengthTakeOffs = $requestBody['lines'];

            $reports = [];
            $totalCosts = 0;
            $totalDays = 0;

            foreach ($countTakeOffs as $count) {
                $countReport = $this->_generateCountReport($count);
                $totalDays += $countReport['totalDays'];
                $totalCosts += $countReport['total'];

                $reports = array_merge($reports, $countReport['reports']);
            }

            foreach ($perimterTakeOffs as $perimeter) {
                $perimeterReport = $this->_generatePerimeterReport($perimeter);
                $totalDays += $perimeterReport['totalDays'];
                $totalCosts += $perimeterReport['total'];

                $reports = array_merge($reports, $perimeterReport['reports']);
            }

            foreach ($lengthTakeOffs as $line) {
                $lengthReport = $this->_generateLengthTakeoff($line);
                $totalDays += $lengthReport['totalDays'];
                $totalCosts += $lengthReport['total'];

                $reports = array_merge($reports, $lengthReport['reports']);
            }

            $crews = json_decode($project->crews);
            $totalCrewCostPerLabor = 0;
            $burdens = [];
            if (count($crews ?? []) > 0) {
                $crew = Crew::find((int) $crews[0]);

                $labor_info = json_decode($crew->labor_info);

                foreach ($labor_info as $lab) {
                    $labor = Labor::find((int) $lab->labor_type_id);
                    $total_hours = ((float) $lab->hours_per_day + (float) $lab->overtime_per_day + (float) $lab->doubletime_per_day);
                    $crew_total_hours = $total_hours * $totalDays;
                    $totalCrewCostPerLabor += $crew_total_hours * $labor->total_cost;
                    $burdens = array_merge($burdens, array_values((array) json_decode($labor->burdens)));
                }

                $crewReport = [
                    'crewName' => $crew->name,
                    'totaldays' => round($totalDays, 2),
                    'crewPerDay' => round($totalCrewCostPerLabor / ($totalDays != 0 ? $totalDays : 1), 2),
                    'crewTotal' => round($totalCrewCostPerLabor, 2),
                    'burdens' => $burdens


                ];
            }

            $equipment_ids = $project->equipments;

            if (count(json_decode($equipment_ids) ?? []) > 0) {
                $equipmentReport = $this->_generateEquipmentReport(json_decode($equipment_ids), $totalDays);
            }

            $sReport = $this->_serializeReport($reports);
            $totalReport = [
                'material' => round($totalCosts, 2),
                'crew' => round($totalCrewCostPerLabor, 2),
                'equipment' => isset($equipmentReport) ? round($equipmentReport['totalCost'], 2) : 0,
                'project_total' => round($totalCosts + $totalCrewCostPerLabor + isset($equipmentReport) ? $equipmentReport['totalCost'] : 0, 2)

            ];

            $totalTax = [
                'material' => 0,
                'crew' => '-',
                'equipment' => '-',
                'project_total' => '-'
            ];

            $ohReport = [
                'material' => $totalReport['material'] * (float) json_decode($project->material_profit_info)->oh / 100,
                'crew' => $totalReport['crew'] * (float) json_decode($project->labor_profit_info)->oh / 100,
                'equipment' => $totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->oh / 100,
                'project_total' => ($totalReport['material'] * (float) json_decode($project->material_profit_info)->oh / 100) + ($totalReport['crew'] * (float) json_decode($project->labor_profit_info)->oh / 100) + ($totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->oh / 100)
            ];

            $profitReport = [
                'material' => $totalReport['material'] * (float) json_decode($project->material_profit_info)->profit / 100,
                'crew' => $totalReport['crew'] * (float) json_decode($project->labor_profit_info)->profit / 100,
                'equipment' => $totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->profit / 100,
                'project_total' => ($totalReport['material'] * (float) json_decode($project->material_profit_info)->profit / 100) + ($totalReport['crew'] * (float) json_decode($project->labor_profit_info)->profit / 100) + ($totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->profit / 100)
            ];

            $finalReport = [
                'material' => $totalReport['material'] + $ohReport['material'] + $profitReport['material'],
                'crew' => $totalReport['crew'] + $ohReport['crew'] + $profitReport['crew'],
                'equipment' => $totalReport['equipment'] + $ohReport['equipment'] + $profitReport['equipment'],
                'project_total' => $totalReport['project_total'] + $ohReport['project_total'] + $profitReport['project_total'],
            ];

            return view('templates.report1', [
                'reports' => $sReport,
                'crewReport' => isset($crewReport) ? $crewReport : null,
                'equipmentReport' => isset($equipmentReport) ? $equipmentReport : null,
                'totalReport' => $totalReport,
                'totalTax' => $totalTax,
                'subTotal' => $totalReport,
                'ohReport' => $ohReport,
                'profitReport' => $profitReport,
                'finalReport' => $finalReport,
                'project' => $project,
                'materialTotalCost' => round($totalCosts, 2),
                'id' => $project->id
            ]);
        } catch (\Exception $err) {
            throw $err;
        }
    }
    public function generate_project_proposal(Request $request, $project_id)
    {
        try {
            $project = Project::find($project_id);

            $requestBody = $request->all();

            $countTakeOffs = $requestBody['counts'];
            $perimterTakeOffs = $requestBody['perimeters'];
            $lengthTakeOffs = $requestBody['lines'];
            $reports = [];
            $totalCosts = 0;
            $totalDays = 0;

            foreach ($countTakeOffs as $count) {
                $countReport = $this->_generateCountReport($count);
                $totalDays += $countReport['totalDays'];
                $totalCosts += $countReport['total'];

                $reports = array_merge($reports, $countReport['reports']);
            }

            foreach ($perimterTakeOffs as $perimeter) {
                $perimeterReport = $this->_generatePerimeterReport($perimeter);
                $totalDays += $perimeterReport['totalDays'];
                $totalCosts += $perimeterReport['total'];

                $reports = array_merge($reports, $perimeterReport['reports']);
            }
            foreach ($lengthTakeOffs as $line) {
                $lengthReport = $this->_generateLengthTakeoff($line);
                $totalDays += $lengthReport['totalDays'];
                $totalCosts += $lengthReport['total'];

                $reports = array_merge($reports, $lengthReport['reports']);
            }

            $crews = json_decode($project->crews);
            $totalCrewCostPerLabor = 0;
            $burdens = [];
            if (count($crews ?? []) > 0) {
                $crew = Crew::find((int) $crews[0]);

                $labor_info = json_decode($crew->labor_info);

                foreach ($labor_info as $lab) {
                    $labor = Labor::find((int) $lab->labor_type_id);
                    $total_hours = ((float) $lab->hours_per_day + (float) $lab->overtime_per_day + (float) $lab->doubletime_per_day);
                    $crew_total_hours = $total_hours * $totalDays;
                    $totalCrewCostPerLabor += $crew_total_hours * $labor->total_cost;
                    $burdens = array_merge($burdens, array_values((array) json_decode($labor->burdens)));
                }

                $crewReport = [
                    'crewName' => $crew->name,
                    'totaldays' => round($totalDays, 2),
                    'crewPerDay' => round($totalCrewCostPerLabor / $totalDays, 2),
                    'crewTotal' => round($totalCrewCostPerLabor, 2),
                    'burdens' => $burdens


                ];
            }

            $equipment_ids = $project->equipments;

            if (count(json_decode($equipment_ids) ?? []) > 0) {
                $equipmentReport = $this->_generateEquipmentReport(json_decode($equipment_ids), $totalDays);
            }

            $sReport = $this->_serializeReport($reports);
            $totalReport = [
                'material' => round($totalCosts, 2),
                'crew' =>  round($totalCrewCostPerLabor, 2),
                'equipment' => isset($equipmentReport) ? round($equipmentReport['totalCost'], 2) : 0,
                'project_total' => round($totalCosts + $totalCrewCostPerLabor + isset($equipmentReport) ? $equipmentReport['totalCost'] : 0, 2)

            ];

            $totalTax = [
                'material' => 0,
                'crew' => '-',
                'equipment' => '-',
                'project_total' => '-'
            ];

            $ohReport = [
                'material' => $totalReport['material'] * (float) json_decode($project->material_profit_info)->oh / 100,
                'crew' => $totalReport['crew'] * (float) json_decode($project->labor_profit_info)->oh / 100,
                'equipment' => $totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->oh / 100,
                'project_total' => ($totalReport['material'] * (float) json_decode($project->material_profit_info)->oh / 100) + ($totalReport['crew'] * (float) json_decode($project->labor_profit_info)->oh / 100) + ($totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->oh / 100)
            ];

            $profitReport = [
                'material' => $totalReport['material'] * (float) json_decode($project->material_profit_info)->profit / 100,
                'crew' => $totalReport['crew'] * (float) json_decode($project->labor_profit_info)->profit / 100,
                'equipment' => $totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->profit / 100,
                'project_total' => ($totalReport['material'] * (float) json_decode($project->material_profit_info)->profit / 100) + ($totalReport['crew'] * (float) json_decode($project->labor_profit_info)->profit / 100) + ($totalReport['equipment'] * (float) json_decode($project->equipment_profit_info)->profit / 100)
            ];

            $finalReport = [
                'material' => $totalReport['material'] + $ohReport['material'] + $profitReport['material'],
                'crew' => $totalReport['crew'] + $ohReport['crew'] + $profitReport['crew'],
                'equipment' => $totalReport['equipment'] + $ohReport['equipment'] + $profitReport['equipment'],
                'project_total' => $totalReport['project_total'] + $ohReport['project_total'] + $profitReport['project_total'],
            ];

            return view('templates.proposal1', [
                'reports' => $sReport,
                'crewReport' => isset($crewReport) ? $crewReport : null,
                'equipmentReport' => isset($equipmentReport) ? $equipmentReport : null,
                'totalReport' => $totalReport,
                'totalTax' => $totalTax,
                'subTotal' => $totalReport,
                'ohReport' => $ohReport,
                'profitReport' => $profitReport,
                'finalReport' => $finalReport,
                'project' => $project,
                'materialTotalCost' => round($totalCosts, 2),
                'id' => $project->id
            ]);
        } catch (\Exception $err) {
            return $err;
        }
    }
    private function _generateEquipmentReport($equipment_id, $totalDays)
    {
        $totalCost = 0;
        $reports = [];
        $totalCost = 0;
        $totalDayRequired = 0;
        // print_r($equipment_id);
        foreach ($equipment_id as $id) {
            $equipment = Equipment::find($id);
            $reports[] =  [
                'name' => $equipment->name,
                'id' => $equipment->unique_id,
                'cost_per_day' => round($equipment->cost_per_day, 2),
                'days' => round($totalDays, 2),
                'cost' => round($totalDays * $equipment->cost_per_day, 2)
            ];
            $totalDayRequired += $totalDays;
            $totalCost += round($totalDays * $equipment->cost_per_day, 2);
        }

        return ['reports' => $reports, 'totalDays' => $totalDayRequired, 'totalCost' => $totalCost];
    }
    private function _serializeReport($reports)
    {
        $sReport = [];
        $sName = [];

        // print_r($reports);

        foreach ($reports as $key => $report) {
            // print_r($sName);
            $index = @$sName[$report['unique_id']];
            if ($index == null) {
                // echo "Array Key Not exists";
                $sReport[$key] = $report;
                $sName[$report['unique_id']] = $key;
            } else {
                // echo "Array Key exists";

                $prevReport = $sReport[$index];
                // print_r($prevReport);
                $newValue = ['unitPrice' => $prevReport['unitPrice'], 'description' => $report['description'], 'unique_id' => $report['unique_id'], 'cost' => $prevReport['cost'] + $report['cost'], 'name' => $prevReport['name'], 'totalUnits' => $prevReport['totalUnits'] + $report['totalUnits'], 'class' => $prevReport['class'], 'days' => $prevReport['days'] + $report['days']];
                // print_r($newValue);
                $sReport[$index] = $newValue;
                // $sName[$report['name']] = $key;
            }
        }
        // print_r($sReport);
        return $sReport;
    }
    private function _generateLengthTakeoff($lines)
    {
        $reports = [];
        $labors = [];
        $totalCost = 0;
        $totalDays = 0;
        // $requestBody =  $request->all();
        $wall_material = $lines['wall_material'];
        $total_length = (float) $lines['totalLength'];
        $total_wall_height = ((float) $lines['total_wall_height']) / 12;

        $total_area = $total_length * $total_wall_height;

        $material_dimension = $wall_material['height'] * $wall_material['length'];

        $required_unit = $total_area / $material_dimension;

        $day_required =  $required_unit / $wall_material['production_rate'];

        $cost = $required_unit *  ((float) $wall_material['prices'] + (float) $wall_material['cleaning_subed_out'] + (float) $wall_material['cleaning_cost'] + (float) $wall_material['production_subed_out_cost']);

        $material_class = MaterialClass::find($wall_material['material_class_id'])->name;
        $reports[] = ['cost' => round($cost, 2), 'unique_id' => $wall_material['unique_id'], 'description' => $wall_material['description'], 'unitPrice' => $wall_material['prices'], 'name' => $wall_material['name'], 'totalUnits' => $required_unit, 'class' => $material_class, 'days' => $day_required];

        $totalCost += round($cost, 2);
        $totalDays += $day_required;
        // foreach ($materials as $material) {
        //     $cost = $counts['count'] *  ((int) $material->prices + (int) $material->cleaning_subbed + (int) $material->cleaning_cost + (int) $material->production_subed_out_cost);
        //     $totalUnits = $counts['count'];
        //     $name = $material->name;
        //     $material_class = $material->material_class->name;
        //     $day_required = $totalUnits / $material->production_rate;
        //     $totalDays += $day_required;
        //     $reports[] = ['cost'=> $cost,'unique_id' => $material->unique_id,'description' => $material->description,'unitPrice' => $material->prices, 'name' => $name, 'totalUnits'=>$totalUnits, 'class' => $material_class, 'days'=>$day_required];
        //     $totalCost += $cost;
        // }

        return ['total' => $totalCost, 'reports' => $reports, 'totalDays' => $totalDays];
    }
    private function _generateCountReport($counts)
    {
        $reports = [];
        $labors = [];
        $totalCost = 0;
        $totalDays = 0;
        // $requestBody =  $request->all();
        $used_materials = array_merge([$counts['main_material']], $counts['additional_material']);
        $materials = Material::where('user_id', Auth::id())->whereIn('name', $used_materials)->get();

        foreach ($materials as $material) {
            $cost = $counts['count'] *  ((int) $material->prices + (int) $material->cleaning_subbed + (int) $material->cleaning_cost + (int) $material->production_subed_out_cost);
            $totalUnits = $counts['count'];
            $name = $material->name;
            $material_class = $material->material_class->name;
            $day_required = $totalUnits / $material->production_rate;
            $totalDays += $day_required;
            $reports[] = ['cost' => $cost, 'unique_id' => $material->unique_id, 'description' => $material->description, 'unitPrice' => $material->prices, 'name' => $name, 'totalUnits' => $totalUnits, 'class' => $material_class, 'days' => $day_required];
            $totalCost += $cost;
        }

        return ['total' => $totalCost, 'reports' => $reports, 'totalDays' => $totalDays];
    }
    private function _generatePerimeterReport($perimeter)
    {
        $reports = [];
        $totalCost = 0;
        $totalDays = 0;
        // $requestBody = $request->all();
        $used_main_material = $perimeter['main_material'];
        $used_additionals = $perimeter['additional_material'];
        $used_perimeter_material = $perimeter['perimeter_material'];

        $required_main = (int) $perimeter['perimeter'] * (int) $perimeter['perimeter_width'] / 12;
        $required_perimeter = (int) $perimeter['perimeter'];


        $main_material = Material::where(['user_id' => Auth::id(), 'name' => $used_main_material])->first();
        $main_material_required = (($main_material->length ?? 0) * ($main_material->width ?? 0)) / $required_main;
        $main_material_cost = (int) $main_material->prices * $main_material_required;
        $main_material_days = $main_material->production_rate / $main_material_required;
        $main_material_class = $main_material->material_class->name;

        $reports[] = ['unitPrice' => $main_material->prices, 'description' => $main_material->description, 'unique_id' => $main_material->unique_id, 'cost' => round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits' => round($main_material_required, 2), 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost += $main_material_cost;
        $totalDays += $main_material_days;

        $perimeter_material = Material::where(['user_id' => Auth::id(), 'name' => $used_perimeter_material])->first();
        $perimeter_material_required = ($perimeter_material->length ?? 0) / $required_perimeter;
        $perimeter_cost = (int) $perimeter_material->prices *  $perimeter_material_required;
        $perimeter_class = $perimeter_material->material_class->name;
        $perimeter_material_days = $perimeter_material_required / $perimeter_material->production_rate;
        $totalCost += $perimeter_cost;
        $totalDays += $perimeter_material_days;
        $reports[] = ['unitPrice' => $perimeter_material->prices, 'description' => $perimeter_material->description, 'unique_id' => $perimeter_material->unique_id, 'cost' => round($perimeter_cost, 2), 'name' => $perimeter_material->name, 'totalUnits' => round($perimeter_material_required, 2), 'class' => $perimeter_class, 'days' => round($perimeter_material_days, 2)];

        // $additional_materials = Material::where('user_id', Auth::id())->whereIn('name', $used_additionals)->get();

        foreach ($used_additionals as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            $required = $required_perimeter / (int) $item['qty'];
            $cost = $required * (int) $material->prices;
            $days = $required / $material->production_rate;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['unitPrice' => $material->prices, 'description' => $material->description, 'unique_id' => $material->unique_id, 'cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }



        return ['total' => round($totalCost, 2), 'reports' => $reports, 'totalDays' => round($totalDays, 2)];
    }
    public function generate_line_report(Request $request)
    {
        $reports = [];
        $totalCost = 0;
        $totalDays = 0;
        $requestBody = $request->all();

        $used_main_material = $requestBody['meta']['main_material'];
        $used_additionals = $requestBody['meta']['additional_material'];
        $used_others = $requestBody['meta']['other_material'];
        $used_deducts = $requestBody['meta']['deduct_material'];

        $required_main = (int) $requestBody['meta']['area'] * ((int) $requestBody['meta']['thickness'] / 12);

        $main_material = Material::where(['user_id' => Auth::id(), 'name' => $used_main_material])->first();
        $main_material_required = (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / $required_main;
        $main_material_cost = (int) $main_material->prices * $main_material_required;
        $main_material_days = @($main_material->production_rate / $main_material_required) ? @($main_material->production_rate / $main_material_required) : 0;
        $main_material_class = $main_material->material_class->name;

        $reports[] = ['cost' => round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits' => round($main_material_required, 2) . ' Sqft', 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost += $main_material_cost;
        $totalDays += $main_material_days;

        foreach ($used_additionals as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['thickness'] / 12);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }
        foreach ($used_deducts as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['quantity']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }
        foreach ($used_others as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['required']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }
        // echo str_replace(['NAN', 'INF'], '0', serialize($reports));die;
        $s_report = unserialize(str_replace([NAN, INF], '0', serialize(['total' => round($totalCost, 2), 'reports' => $reports, 'totalDays' => round($totalDays, 2)])));
        return $s_report;
    }
    public function generate_area_report(Request $request, $project_id)
    {
        $reports = [];
        $totalCost = 0;
        $totalDays = 0;
        $requestBody = $request->all();

        $used_main_material = $requestBody['meta']['main_material'];
        $used_additionals = $requestBody['meta']['additional_material'];
        $used_others = $requestBody['meta']['other_material'];
        $used_deducts = $requestBody['meta']['deduct_material'];

        $required_main = (int) $requestBody['meta']['area'] * ((int) $requestBody['meta']['thickness'] / 12);

        $main_material = Material::where(['user_id' => Auth::id(), 'name' => $used_main_material])->first();
        $main_material_required = (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / $required_main;
        $main_material_cost = (int) $main_material->prices * $main_material_required;
        $main_material_days = @($main_material->production_rate / $main_material_required) ? @($main_material->production_rate / $main_material_required) : 0;
        $main_material_class = $main_material->material_class->name;

        $reports[] = ['cost' => round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits' => round($main_material_required, 2), 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost += $main_material_cost;
        $totalDays += $main_material_days;

        foreach ($used_additionals as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            if ($material == null) {
                continue;
            }
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['thickness'] / 12);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => $days];
        }
        foreach ($used_deducts as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            if ($material == null) {
                continue;
            }
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['quantity']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => $days];
        }
        foreach ($used_others as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            if ($material == null) {
                continue;
            }
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['required']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => $days];
        }
        // echo str_replace(['NAN', 'INF'], '0', serialize($reports));die;

        $project = Project::find($project_id);
        $crews = json_decode($project->crews);
        $totalCrewCostPerLabor = 0;


        if (count($crews ?? []) > 0) {
            $crew = Crew::find((int) $crews[0]);

            $labor_info = json_decode($crew->labor_info);

            foreach ($labor_info as $lab) {
                $labor = Labor::find((int) $lab->labor_type_id);
                $total_hours = ((float) $lab->hours_per_day + (float) $lab->overtime_per_day + (float) $lab->doubletime_per_day);
                $crew_total_hours = $total_hours * $totalDays;
                $totalCrewCostPerLabor += $crew_total_hours * $labor->total_cost;
            }

            $crewReport = [
                'crewName' => $crew->name,
                'totaldays' => $totalDays,
                'crewPerDay' => round($totalCrewCostPerLabor / $totalDays, 2),
                'crewTotal' => $totalCrewCostPerLabor


            ];
        }
        $s_report = unserialize(str_replace([NAN, INF], '0', serialize(['total' => round($totalCost, 2), 'reports' => $reports, 'totalDays' => round($totalDays, 2), 'crewReport' => isset($crewReport) ? $crewReport : null, 'project' => $project])));
        return $s_report;
    }
    public function generate_perimeter_report(Request $request, $project_id)
    {
        $reports = [];
        $totalCost = 0;
        $totalDays = 0;
        $requestBody = $request->all();
        $used_main_material = $requestBody['meta']['main_material'];
        $used_additionals = $requestBody['meta']['additional_material'];
        $used_perimeter_material = $requestBody['meta']['perimeter_material'];

        $required_main = (int) $requestBody['meta']['perimeter'] * (int) $requestBody['meta']['perimeter_width'] / 12;
        $required_perimeter = (int) $requestBody['meta']['perimeter'];


        $main_material = Material::where(['user_id' => Auth::id(), 'name' => $used_main_material])->first();
        $main_material_required = (($main_material->length ?? 0) * ($main_material->width ?? 0)) / $required_main;
        $main_material_cost = (int) $main_material->prices * $main_material_required;
        $main_material_days = $main_material->production_rate / $main_material_required;
        $main_material_class = $main_material->material_class->name;

        $reports[] = ['cost' => round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits' => round($main_material_required, 2) . '', 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost += $main_material_cost;
        $totalDays += $main_material_days;

        $perimeter_material = Material::where(['user_id' => Auth::id(), 'name' => $used_perimeter_material])->first();
        $perimeter_material_required = ($perimeter_material->length ?? 0) / $required_perimeter;
        $perimeter_cost = (int) $perimeter_material->prices *  $perimeter_material_required;
        $perimeter_class = $perimeter_material->material_class->name;
        $perimeter_material_days = $perimeter_material_required / $perimeter_material->production_rate;
        $totalCost += $perimeter_cost;
        $totalDays += $perimeter_material_days;
        $reports[] = ['cost' => round($perimeter_cost, 2), 'name' => $perimeter_material->name, 'totalUnits' => round($perimeter_material_required, 2), 'class' => $perimeter_class, 'days' => round($perimeter_material_days, 2)];

        // $additional_materials = Material::where('user_id', Auth::id())->whereIn('name', $used_additionals)->get();

        foreach ($used_additionals as $item) {
            $material = Material::where([
                'user_id' => Auth::id(),
                'name' => $item['name']
            ])->first();
            $required = $required_perimeter / (int) $item['qty'];
            $cost = $required * (int) $material->prices;
            $days = $required / $material->production_rate;
            $totalCost += $cost;
            $totalDays += $days;
            $reports[] = ['cost' => round($cost, 2), 'name' => $material->name, 'totalUnits' => round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }

        $project = Project::find($project_id);
        $crews = json_decode($project->crews);
        $totalCrewCostPerLabor = 0;


        if (count($crews ?? []) > 0) {
            $crew = Crew::find((int) $crews[0]);

            $labor_info = json_decode($crew->labor_info);

            foreach ($labor_info as $lab) {
                $labor = Labor::find((int) $lab->labor_type_id);
                $total_hours = ((float) $lab->hours_per_day + (float) $lab->overtime_per_day + (float) $lab->doubletime_per_day);
                $crew_total_hours = $total_hours * $totalDays;
                $totalCrewCostPerLabor += $crew_total_hours * $labor->total_cost;
            }

            $crewReport = [
                'crewName' => $crew->name,
                'totaldays' => $totalDays,
                'crewPerDay' => round($totalCrewCostPerLabor / $totalDays, 2),
                'crewTotal' => $totalCrewCostPerLabor


            ];
        }

        return ['total' => round($totalCost, 2), 'reports' => $reports, 'totalDays' => round($totalDays, 2), 'crewReport' => isset($crewReport) ? $crewReport : null, 'project' => $project];
    }
    public function generate_count_report(Request $request, $project_id)
    {
        $reports = [];
        $labors = [];
        $totalCost = 0;
        $totalDays = 0;
        $requestBody =  $request->all();
        $used_materials = array_merge([$requestBody['meta']['main_material']], $requestBody['meta']['additional_material']);
        $materials = Material::where('user_id', Auth::id())->whereIn('name', $used_materials)->get();

        foreach ($materials as $material) {
            $cost = count($requestBody['path']) *  ((int) $material->prices + (int) $material->cleaning_subbed + (int) $material->cleaning_cost + (int) $material->production_subed_out_cost);
            $totalUnits = count($requestBody['path']);
            $name = $material->name;
            $material_class = $material->material_class->name;
            $day_required = $totalUnits / $material->production_rate;
            $totalDays += $day_required;
            $reports[] = ['cost' => $cost, 'name' => $name, 'totalUnits' => $totalUnits, 'class' => $material_class, 'days' => $day_required];
            $totalCost += $cost;
        }

        $project = Project::find($project_id);
        $crews = json_decode($project->crews);
        $totalCrewCostPerLabor = 0;


        if (count($crews ?? []) > 0) {
            $crew = Crew::find((int) $crews[0]);

            $labor_info = json_decode($crew->labor_info);

            foreach ($labor_info as $lab) {
                $labor = Labor::find((int) $lab->labor_type_id);
                $total_hours = ((float) $lab->hours_per_day + (float) $lab->overtime_per_day + (float) $lab->doubletime_per_day);
                $crew_total_hours = $total_hours * $totalDays;
                $totalCrewCostPerLabor += $crew_total_hours * $labor->total_cost;
            }

            $crewReport = [
                'crewName' => $crew->name,
                'totaldays' => $totalDays,
                'crewPerDay' => round($totalCrewCostPerLabor / $totalDays, 2),
                'crewTotal' => $totalCrewCostPerLabor


            ];
        }





        return ['total' => $totalCost, 'reports' => $reports, 'totalDays' => $totalDays, 'crewReport' => isset($crewReport) ? $crewReport : null, 'project' => $project];
    }
    public function login(Request $request)
    {
        $credentials = [
            'username' => $request->post('username'),
            'password' => $request->post('password')
        ];
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user->token = User::find(Auth::id())->remember_token;
            return $user;
        }

        return json_encode([
            'status' => 403,
            'message' => 'Authentication Failed!'
        ]);
    }

    public function me()
    {
        return Auth::user();
    }
    public function allProjects()
    {
        $projects = Project::where('user_id', Auth::id())->get();
        return $projects;
    }
    public function projects()
    {
        $projects = Project::where('user_id', Auth::id())->withCasts([
            'materials' => 'json',
            'crews' => 'json',
            'equipments' => 'json',
            'items' => 'json',
            'material_profit_info' => 'json',
            'labor_profit_info' => 'json',
            'equipment_profit_info' => 'json',
            'subcontractor_profit_info' => 'json',
            'other_profit_info' => 'json'
        ])->get();
        return $projects;
    }

    public function project($id)
    {
        $project = Project::find($id)->mergeCasts([
            'materials' => 'json',
            'crews' => 'json',
            'equipments' => 'json',
            'items' => 'json',
            'material_profit_info' => 'json',
            'labor_profit_info' => 'json',
            'equipment_profit_info' => 'json',
            'subcontractor_profit_info' => 'json',
            'other_profit_info' => 'json'
        ]);

        return $project;
    }

    public function projectName($id)
    {
        $project = Project::where('id', $id)->select('name', 'bid_number', 'bid_date', 'bid_time')->first();

        if ($project->bid_date) {
            // Format the bid_date using Carbon
            $formattedDate = Carbon::parse($project->bid_date)->format('d-m-Y');

            // Update the bid_date attribute with the formatted date
            $project->bid_date = $formattedDate;
        }
        return $project;
    }

    public function labors()
    {
        $labors = Labor::where('user_id', Auth::id())->withCasts([
            'burdens' => 'json'
        ])->get();

        return $labors;
    }

    public function labor($id)
    {
        $labor = Labor::find($id)->mergeCasts([
            'burdens' => 'json'
        ]);

        return $labor;
    }

    public function equipments()
    {
        $equipments = Equipment::where('user_id', Auth::id())->get();

        return $equipments;
    }

    public function equipment($id)
    {
        $equipment = Equipment::find($id);

        return $equipment;
    }

    public function crews()
    {
        $crews = Crew::where('user_id', Auth::id())->withCasts([
            'equipment_ids' => MakeEquipment::class,
            'labor_info' => 'json'
        ])->get();

        return $crews;
    }

    public function crew($id)
    {
        $crew = Crew::find($id)->mergeCasts([
            'equipment_ids' => MakeEquipment::class,
            'labor_info' => 'json'
        ]);

        return $crew;
    }
    public function material_divisions()
    {
        $material_divisions = MaterialDivision::orderBy('id', 'desc')->get()->load(['materials' => function ($query) {
            $query->where('user_id', Auth::id());
        }]);
        return $material_divisions;
    }
    public function material_from_division($id)
    {
        $materials = MaterialDivision::find($id)->materials;

        return $materials;
    }
    public function materials($project_id = null)
    {
        $materials = Material::select(
            'id',
            'user_id',
            'name',
            'material_class_id',
            'material_division_id',
            'unique_id',
            'default_unit',
            'description',
            'measurement_unit',
            'height',
            'width',
            'length',
            'prices',
            'waste',
            'production_rate',
            'production_subed_out_cost',
            'cleaning_cost',
            'cleaning_subed_out',
            'associated_products',
            'subbed_out_rate',
            'created_at',
            'updated_at',
            'project_id',
            'material_type_id',
            'unit_measure_value',
            'shortton_wlf',
            'weight_lf',
            'sq_ft_per_cy',
        )
            ->where('user_id', Auth::id())->orderBy('name', 'asc')->where('project_id', $project_id)->withCasts([
                'prices' => 'json',
                'associated_products' => 'json'
            ])->get();

        return $materials;
    }

    public function material($id)
    {
        $material = material::find($id)->mergeCasts([
            'prices' => 'json',
            'associated_products' => 'json'
        ]);

        return $material;
    }
    private function getPDFTotalPages($document)
    {
        // $cmd = "/path/to/pdfinfo";           // Linux
        $cmd = "LD_LIBRARY_PATH=" . env('LIB64_PATH') . '; pdfinfo';  // Windows

        // Parse entire output
        // Surround with double quotes if file name has spaces
        exec("$cmd \"$document\"", $output);

        // Iterate through lines
        $pagecount = 0;
        echo "$cmd \"$document\"";
        foreach ($output as $op) {
            // Extract the number
            if (preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
                $pagecount = intval($matches[1]);
                break;
            }
        }


        return $pagecount;
    }

    private function extractSelectedPages($sourcePdf, $selectedPages, $outputPdf)
    {
        $pdf = new Fpdi();

        // Cargar PDF original
        $pageCount = $pdf->setSourceFile($sourcePdf);

        foreach ($selectedPages as $pageNum) {

            if ($pageNum > 0 && $pageNum <= $pageCount) {

                // Importar página
                $tpl = $pdf->importPage($pageNum);

                // Obtener tamaño de página
                $size = $pdf->getTemplateSize($tpl);

                // Crear página nueva con el tamaño correspondiente
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

                // Añadir contenido
                $pdf->useTemplate($tpl);
            }
        }

        // Guardar PDF recortado
        $pdf->Output( 'F', $outputPdf);

        return $outputPdf;
    }

    //   PLAN UPLOAD (PDF YA RECORTADO EN FRONT)
    // ============================
    public function plan_upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'error' => 'No file detected'
            ], 400);
        }

        $request->validate([
            'file' => 'mimes:pdf'
        ]);

        try {
            $project_id = $request->post('project_id');
            $file = $request->file('file');

            // Carpeta destino
            $directory = public_path("uploads/projects/project{$project_id}/");

            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0777, true, true);
            }

            // ✅ Nombre final = nombre original del PDF
            $originalName = $file->getClientOriginalName();

            // (opcional pero recomendado) sanitiza nombre para Windows/servidor
            $safeName = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]+/', '-', $originalName);

            // Si quieres evitar colisiones (mismo nombre), agrega timestamp sin cambiar el nombre base:
            // $safeName = pathinfo($safeName, PATHINFO_FILENAME) . '_' . time() . '.pdf';

            $finalPdfPath = $directory . $safeName;

            // Guardar directo (sin FPDI)
            $file->move($directory, $safeName);

            return response()->json([
                'status'   => 200,
                'message'  => "PDF recibido correctamente",
                'pdf_path' => "uploads/projects/project{$project_id}/{$safeName}",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //working code for uploading pdf files in local storage
    // public function plan_upload(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'mimes:pdf'
    //     ]);

    //     $project_id = $request->post('project_id');
    //     $file = $request->file('file');
    //     $filename = $file->getClientOriginalName();
    //     $hashname = \str_replace(['/', '#', '?', '\\'], '', $filename);
    //     $relativePath = $request->post('path');
    //     $path = \str_replace($filename, '', $relativePath);

    //     $document = Project::find($project_id)->document;
    //     if (!$document) {
    //         $directory = \generate_unique_dir();
    //         $isSuccess = Document::create([
    //             'project_id' => $project_id,
    //             'directory' => $directory
    //         ]);

    //         if (!$isSuccess) {
    //             return \json_encode([
    //                 'status' => 500
    //             ]);
    //         }
    //     } else {
    //         $directory = $document->directory;
    //     }
    //     $dirpath = public_path("uploads/projects/$directory/$path");
    //     File::ensureDirectoryExists($dirpath);
    //     $tmpdir = \public_path("uploads/tmp");
    //     $file->move($tmpdir, $hashname);
    //     $tmppath = $tmpdir.'/'.$hashname;
    //     $totalPages = $this->getPDFTotalPages($tmppath);

    //     for ($i = 1; $i <= $totalPages; $i++) {
    //         $filepath = $dirpath.str_replace('.pdf', "-$i.svg", $hashname);
    //         $cmd = 'pdftocairo -svg -f '.$i.' -l '.$i.'  "'.$tmppath.'"  "'.$filepath.'"';
    //         $code = \shell_exec($cmd);
    //     }
    //     @unlink($tmppath);
    //     return json_encode([
    //         'status' => 200,
    //     ]);
    // }

    // Testing code for scanned pdf files and texted pdf files
    // public function plan_upload(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'mimes:pdf'
    //     ]);

    //     $project_id = $request->post('project_id');
    //     $file = $request->file('file');
    //     $filename = $file->getClientOriginalName();
    //     $hashname = \str_replace(['/', '#', '?', '\\'], '', $filename);
    //     $relativePath = $request->post('path');
    //     $path = \str_replace($filename, '', $relativePath);

    //     $document = Project::find($project_id)->document;
    //     if (!$document) {
    //         $directory = \generate_unique_dir();
    //         $isSuccess = Document::create([
    //             'project_id' => $project_id,
    //             'directory' => $directory
    //         ]);

    //         if (!$isSuccess) {
    //             return \json_encode([
    //                 'status' => 500
    //             ]);
    //         }
    //     } else {
    //         $directory = $document->directory;
    //     }
    //     $dirpath = public_path("uploads/projects/$directory/$path");
    //     File::ensureDirectoryExists($dirpath);
    //     $tmpdir = \public_path("uploads/tmp");
    //     $file->move($tmpdir, $hashname);
    //     $tmppath = $tmpdir.'/'.$hashname;
    //     $totalPages = $this->getPDFTotalPages($tmppath);

    //     // Use pdftotext to attempt text extraction
    //      $text = shell_exec("pdftotext \"$tmppath\" -");
    // //  dd($text);
    //        // Check if the extracted text is empty or mostly unreadable
    //         if (empty($text) || strlen($text) < 10) {
    //             $pdfType = 'Scanned PDF';
    //         } elseif (preg_match('/^\s*\\f+\s*$/', $text)) {
    //             $pdfType = 'Scanned PDF'; // Text is mostly "\f\f\f"
    //         } else {
    //             $pdfType = 'Text-based PDF';
    //         }

    //         dd($pdfType);

    //     @unlink($tmppath);
    //     return json_encode([
    //         'status' => 200,
    //     ]);
    // }

    // Checking either file text pdf or scanned pdf
    // public function plan_upload(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'mimes:svg'
    //     ]);
    //      // Get the relevant data from the request
    //      $file = $request->file('file');
    //      $tmpdir = public_path('uploads/tmp');
    //      $filename = $file->getClientOriginalName();
    //      $hashname = \str_replace(['/', '#', '?', '\\'], '', $filename);
    //      $tmpFilePath = $tmpdir . '/' . $hashname;

    //      $file->move($tmpdir, $hashname);

    //      // Use pdftotext to attempt text extraction
    //      $text = shell_exec("pdftotext \"$tmpFilePath\" -");

    //      // Check if the extracted text is empty or mostly unreadable
    //      if (empty($text) || strlen($text) < 10) {
    //          $pdfType = 'Scanned PDF';
    //          dd($pdfType);
    //         } else {
    //             $pdfType = 'Text-based PDF';
    //             dd($pdfType);
    //      }

    //      // Clean up temporary file
    //      @unlink($tmpFilePath);

    //      return json_encode(['pdfType' => $pdfType]);

    // }

    public function project_plans($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return [];
        }

        // Ruta en public/
        $path = public_path("uploads/projects/project{$id}");

        if (!is_dir($path)) {
            return [];
        }

        // Leer archivos reales
        $files = array_values(array_diff(scandir($path), ['.', '..']));

        // Convertir a rutas accesibles desde frontend
        $response = array_map(function($file) use ($id) {
            return "uploads/projects/project{$id}/{$file}";
        }, $files);

        return $response;
    }

    public function delete_all_files($id)
    {
        $project = Project::find($id);

        if (!$project || !$project->document) {
            return response()->json([
                'status' => 500,
                'message' => 'No files to delete'
            ]);
        }

        // Asegurar que la ruta SIEMPRE sea: uploads/projects/project{id}
        $directory = "project" . $project->id;

        $relativePath = public_path("uploads/projects/$directory");

        if (File::exists($relativePath)) {
            File::cleanDirectory($relativePath);
        }

        return response()->json([
            'status' => 200,
            'message' => 'All Files Deleted!'
        ]);
    }

    public function serialize_form(Request $request)
    {
        $id = $request->post('trade');
        if ($id) {
            $material_division = MaterialDivision::find($id);
            $_POST['trade'] = $material_division->name;
        }
        return $_POST;
    }
    public function delete_plans(Request $request)
    {
        $path = $request->post('path');

        @unlink(public_path("uploads/$path"));

        return \json_encode([
            'status' => 200
        ]);
    }
    public function delete_folder(Request $request)
    {
        $path = $request->post('path');
        $directory = Project::find($request->post('project_id'))->document->directory;
        $relativePath = "uploads/projects/$directory$path";
        File::deleteDirectory(public_path($relativePath));

        return \json_encode([
            'status' => 200,
            'relativePath' => $relativePath
        ]);
    }

    // public function sync_local_db(Request $request)
    // {
    //     $data = $request->post("blob");

    //     $user = User::find(Auth::id())->update([
    //         'local_db' => $data
    //     ]);

    //     return [
    //         'status'=> true
    //     ];
    // }
    public function current_location(Request $request)
    {
        $currentpath =  $request->all();
        $fileLocation = $currentpath['fileLocation'];
        $windowLocation = $currentpath['windowLocation'];
        $user = User::find(Auth::id())->update([
            'current_location_file' =>  $fileLocation,
            'current_location' =>  $windowLocation,
        ]);
        return [
            'status' => true
        ];
    }
    public function sync_local_db(Request $request)
    {
        $data =  $request->json()->all();
        $user = User::find(Auth::id())->update([
            'local_db' =>  $data
        ]);

        return [
            'status' => true
        ];
    }
    public function sync_project_local_db(Request $request)
    {
        $projectFolder = $request->json('data.projectFolderName');
        $document = Document::where('directory', $projectFolder)->first();
        // dd($document);
        if ($document != null) {
            $data =  $request->json()->all();
            $document = Document::where('directory', $projectFolder)->update([
                'local_db' =>  $data
            ]);
        } else {
            $data =  $request->json()->all();
            $project_id = str_replace("project", "",  $projectFolder);
            $doc = new Document();
            $doc->local_db = $data;
            $doc->project_id = $project_id;
            $doc->directory = $projectFolder;
            $doc->save();
        }

        return [
            'status' => true
        ];
    }
    public function get_local_db()
    {
        $user = User::find(Auth::id());

        return [
            'data' => $user->local_db
        ];
    }
    public function get_project_local_db($data)
    {
        $data = str_replace("project", "",  $data);
        // echo $data;
        $document = Document::where('project_id', $data)->first();
        if ($document != null) {
            return [
                'data' => $document->local_db
            ];
        } else {
            [
                'data' => []
            ];
        }
    }

    function createProject(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'bid_date' => 'required|date',
        ]);
        $user_id = Auth::id();
        $name = $validatedData['name'];

        // Check if a project with the same name and user_id exists
        $existingProject = Project::where('user_id', $user_id)->where('name', $name)->first();
        if ($existingProject) {
            return json_encode([
                'status' => 200,
                'url' => route('project.measurement', ['id' => $existingProject->id]),
            ]);
        } else {
            $project = new Project();
            $project->name = $name;
            $project->bid_date = $validatedData['bid_date'];
            $project->user_id = $user_id;
            $project->save();
            return json_encode([
                'status' => 200,
                'url' => route('project.measurement', ['id' => $project->id]),
            ]);
        }
    }
    function projectMeasurementData(Request $request)
    {
        $projectName = $request->project_name;
        $user_id = Auth::id();
        $projectData = $request->all();

        // Find the project by name and user_id.
        $project = Project::where('name', $projectName)
            ->where('user_id', $user_id)
            ->first();
        // If the project exists, update the 'project_measurement' column.
        if ($project) {
            $project->project_measurement = json_encode($projectData);
            $project->update();
            return json_encode([
                'status' => 200,
            ]);
        } else {
            return json_encode([
                'status' => 400,
            ]);
        }
    }

    function getMeasurementData($name)
    {
        // dd($name);
        $user_id = Auth::id();
        $project = Project::where('user_id', $user_id)
            ->where('name', $name)
            ->first()->project_measurement;
        if ($project) {
            return json_encode([
                'status' => 200,
                'project' => $project,
            ]);
        } else {
            return json_encode([
                'status' => 400,
            ]);
        }
    }
    public function saveLineTemplate(Request $request)
    {
        $data =  $request->all();
        $templateName = $data['template_name'];
        $tradeName = json_decode($data['trade_name'])->name;
        $templateType = $data['template_type'];
        // $formData =  $request->json()->all();
        $formData = json_encode($data['formData']);
        // dd($formData);
        $user = auth()->user();
        $userId = $user->id;
        $checkTemplate = LineTemplate::where('user_id', $userId)
            ->where('template_name', $templateName)
            ->where('trade_name', $tradeName)
            ->first();
        // dd($checkTemplate);

        // echo($data['formData']['additionalDatas']);
        // $data->additionalDatas = json_decode($data['formData']['additionalDatas']);
        $data['formData']['user_id'] = $userId;

        Wall::create($data['formData']);

        if (!$checkTemplate) {
            // dd('in if condition');
            $lineTemplate = new LineTemplate();
            $lineTemplate->user_id = $userId;
            $lineTemplate->template_name = $templateName;
            $lineTemplate->trade_name = $tradeName;
            $lineTemplate->local_db = $formData;
            $lineTemplate->template_type = $templateType;
            $lineTemplate->save();



            return json_encode([
                'status' => 200,
            ]);
        } else {
            return json_encode([
                'status' => 400,
            ]);
        }
    }

    public function CalculateData(Request $request)
    {
        $procesado=false;
        $data =  $request->all();
        $projectId = $data['projectId'];
        $templateName = $data['template_name'];
        $tradeName = $data['trade_name'];
        $type = ($data['type'] !== null) ? $data['type'] : "length";
        // $formData =  $request->json()->all();
        $formData = json_encode($data['formData']);
        //var_dump($formData);
        // dd($formData);
        $user = auth()->user();
        $userId = $user->id;
        $existing_wall = Wall::where('user_id', $userId)
            ->where('name', $templateName)
            ->first();
        // dd($checkTemplate);

        // echo($data['formData']['additionalDatas']);
        // $data->additionalDatas = json_decode($data['formData']['additionalDatas']);
        $data['formData']['user_id'] = $userId;
        $data['formData']['project_id'] = $projectId;
        $data['formData']['type'] = $type;
        $data['formData']['formData'] = $formData;

        if (!isset($existing_wall->id)) {


            $resultado =  Wall::create($data['formData']);
        } else {
            $resultado =  $existing_wall->update($data['formData']);
        }
        $existing_wall = Wall::where('user_id', $userId)
            ->where('name', $templateName)
            ->first();
         
        $wc =   new WallController;
        $status = 200;

        if (isset($existing_wall->id)) {
            $resultado = $wc->recalculate($existing_wall->id);
           
            try {
                //code...
                $procesado=true;
            } catch (\Throwable $th) {
                //throw $th;
                $status = 400;
                $resultado = $th->getMessage();
            }
        } else {
            
            $resultado = $data['formData'];
        }

      

        return json_encode([
            'status' => $status,
            'resultado' => $resultado,
            'procesado' => $procesado,
            'templateName' => $templateName,
            'userId' => $userId,
        ]);
    }

    public function ObtenData(Request $request)
    {
        $data =  $request->all();
        $projectId = $data['projectId'];
        $templateName = isset($data['template_name']) ? $data['template_name']: "" ;
        $tradeName = $data['trade_name'];
        // $formData =  $request->json()->all();

        // dd($formData);
        $user = auth()->user();
        $userId = $user->id;
        $existing_wall = Wall::where('user_id', $userId)
            ->where('name', $tradeName)
            ->first();
        // dd($checkTemplate);

        // echo($data['formData']['additionalDatas']);
        // $data->additionalDatas = json_decode($data['formData']['additionalDatas']);
        $data['formData']['user_id'] = $userId;
        $data['formData']['project_id'] = $projectId;

        $existing_wall = Wall::where('user_id', $userId)
            ->where('name', $tradeName)
            ->first();

        $wc =   new WallController;
        $status = 200;

        if (isset($existing_wall->id)) {
            $resultado = $wc->recalculate($existing_wall->id);
            try {
                //code...

            } catch (\Throwable $th) {
                //throw $th;
                $status = 400;
                $resultado = $th->getMessage();
            }
        } else {

            $resultado = null;
        }



        return json_encode([
            'status' => $status,
            'resultado' => $resultado,
            'templateName' => $templateName,
            'tradeName' => $tradeName,
            'userId' => $userId,
        ]);
    }
    public function getLineTemplate(Request $request)
    {
        $type = $request->query('type_template');
        $user = auth()->user();
        $userId = $user->id;
        $query = LineTemplate::where('user_id', $userId);
        if (!empty($type)) {
            $query->where('template_type', $type);
        }
        $checkTemplate = $query->get();
        return json_encode([
            'status' => 200,
            'template' => $checkTemplate,
        ]);
    }
    public function logout()
    {
        Auth::logout();
        session()->flush();
        return json_encode([
            'status' => 200,
        ]);
    }

    public function syncTable(Request $request, $table)
    {
        $data = $request->all();

        if (empty($data['id'])) {
            return response()->json(['error' => 'Falta el ID'], 400);
        }

        // convertir arrays a JSON
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = json_encode($value);
            }
        }

        // 🔹 Asegurar formato de fecha
        if (isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s', strtotime($data['updated_at']));
        } else {
            $data['updated_at'] = now();
        }

        $id = $data['id'];
        $row = \DB::table($table)->where('id', $id)->first();

        if ($row) {
            if (strtotime($data['updated_at']) > strtotime($row->updated_at)) {
                \DB::table($table)->where('id', $id)->update($data);
                return response()->json(['status' => 'updated']);
            } else {
                return response()->json(['status' => 'skipped', 'reason' => 'Registro más reciente en servidor']);
            }
        } else {
            \DB::table($table)->insert($data);
            return response()->json(['status' => 'inserted']);
        }
    }

    public function pullUpdates(Request $request, $table)
    {
        $lastUpdated = $request->input('last_updated_at');

        if ($lastUpdated) {
            $lastUpdated = date('Y-m-d H:i:s', strtotime($lastUpdated));
            $records = \DB::table($table)
                ->where('updated_at', '>', $lastUpdated)
                ->get();
        } else {
            $records = \DB::table($table)->get();
        }

        return response()->json([
            'records' => $records,
        ]);
    }

    public function api_course_bands_index(Request $request)
    {
        $q = DB::table('course_band')
            ->select('id', 'id_local', 'id_user', 'data', 'created_at', 'updated_at');

        if ($request->filled('id_user')) {
            $q->where('id_user', (int) $request->query('id_user'));
        }

        if ($request->filled('updated_after')) {
            $dt = Carbon::parse($request->query('updated_after'));
            $q->where('updated_at', '>', $dt);
        }

        $perPage = (int) $request->query('per_page', 100);

        if ($perPage > 0) {
            $rows = $q->orderByDesc('id')->paginate($perPage);
            return response()->json($rows);
        }

        $rows = $q->orderByDesc('id')->limit(1000)->get();
        return response()->json($rows);
    }

    /**
     * POST /api/course-bands
     * Upsert por (id_user, id_local).
     * Acepta:
     *  - Objeto plano: { id_user, id_local, data, created_at?, updated_at? }
     *  - o { items: [ {...}, {...} ] }
     */
    public function api_course_bands_upsert(Request $request)
    {
        $payload = $request->all();
        $items   = Arr::get($payload, 'items');
        $userId  = auth()->id();
        $now     = Carbon::now();

        if (is_null($items)) {
            $items = [$payload]; // normaliza objeto plano -> arreglo
        }
        if (!is_array($items) || empty($items)) {
            throw ValidationException::withMessages([
                'items' => ['Debes enviar un objeto {id_local,data} o {items:[...]}']
            ]);
        }

        $results = ['inserted' => 0, 'updated' => 0, 'items' => []];

        foreach ($items as $row) {
            // id_local opcional; si no llega, generamos uno (UUID)
            $idLocal = (string) Arr::get($row, 'id_local', Str::uuid()->toString());
            $data    = Arr::get($row, 'data', []);

            // si data llega como string JSON, decodifica; si es array, lo dejamos
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $decoded;
                }
            }

            // serializa data a JSON para guardarlo
            $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

            // ¿ya existe por (id_user, id_local)?
            $existing = DB::table('course_band')
                ->select('id')
                ->where('id_user', $userId)
                ->where('id_local', $idLocal)
                ->first();

            if ($existing) {
                // UPDATE (con CAST a JSON para columnas JSON)
                DB::statement(
                    "UPDATE course_band
                    SET data = CAST(? AS JSON), updated_at = ?
                    WHERE id = ? AND id_user = ?",
                    [$dataJson, $now, $existing->id, $userId]
                );

                $results['updated']++;
                $results['items'][] = [
                    'id'       => $existing->id,
                    'id_user'  => $userId,
                    'id_local' => $idLocal,
                ];
            } else {
                // INSERT
                DB::statement(
                    "INSERT INTO course_band (id_user, id_local, data, created_at, updated_at)
                    VALUES (?, ?, CAST(? AS JSON), ?, ?)",
                    [$userId, $idLocal, $dataJson, $now, $now]
                );

                // recupera el id insertado
                $newId = DB::getPdo()->lastInsertId();

                $results['inserted']++;
                $results['items'][] = [
                    'id'       => (int)$newId,
                    'id_user'  => $userId,
                    'id_local' => $idLocal,
                ];
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Upsert completado',
            'result'  => $results
        ]);
    }

    /**
     * PUT /api/course-bands/{id}
     * Update directo por ID real de DB (útil si ya lo tienes en el front).
     */
    public function api_course_bands_update(Request $request, $id)
    {
        $userId = auth()->id();
        $now    = Carbon::now();

        // validar existencia y propiedad del registro
        $row = DB::table('course_band')
            ->select('id', 'id_local')
            ->where('id', $id)
            ->where('id_user', $userId)
            ->first();

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'No encontrado'], 404);
        }

        $data    = Arr::get($request->all(), 'data', []);
        $idLocal = Arr::get($request->all(), 'id_local'); // opcional

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            }
        }
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        // si mandan id_local nuevo, úsalo; si no, conserva el actual
        $idLocalNew = $idLocal ? (string)$idLocal : $row->id_local;

        // OJO: si cambias id_local, asegúrate de no romper el UNIQUE (id_user,id_local)
        DB::statement(
            "UPDATE course_band
            SET id_local = ?, data = CAST(? AS JSON), updated_at = ?
            WHERE id = ? AND id_user = ?",
            [$idLocalNew, $dataJson, $now, $row->id, $userId]
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Actualizado',
            'item'    => [
                'id'       => (int)$row->id,
                'id_user'  => $userId,
                'id_local' => $idLocalNew,
            ]
        ]);
    }

    public function searchPitch()
    {
        $pitches = DB::table('pitch')
            ->select('id', 'name', 'value')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $pitches
        ]);
    }

    public function savePlanImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:png',
            'project_id' => 'required|integer',
            'file_name' => 'required|string'
        ]);

        $projectId = $request->project_id;
        $fileName  = $request->file_name;

        $directory = public_path("uploads/projects/project{$projectId}/");

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0777, true, true);
        }

        // Guardar PNG final
        $request->file('image')->move($directory, $fileName);

        return response()->json([
            "status" => 200,
            "message" => "PNG guardada correctamente",
            "path" => "uploads/projects/project{$projectId}/{$fileName}"
        ]);
    }

    public function convert(Request $request)
    {
        $pdfRelative = $request->input('pdf');   // ejemplo: projects/myproject/file.pdf
        $outputDir   = dirname($pdfRelative);    // carpeta donde dejaremos los PNG
        $pdfPath     = public_path('uploads/' . $pdfRelative);

        if (!file_exists($pdfPath)) {
            return response()->json(['error' => 'PDF not found'], 404);
        }

        $outputPath = public_path('uploads/' . $outputDir);

        // Crear directorio si no existe
        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        // Cargar PDF completo
        $imagick = new Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage($pdfPath);

        $pages = $imagick->getNumberImages();
        $pageIndex = 0;
        $generated = [];

        foreach ($imagick as $index => $page) {

            $page->setImageFormat('png');

            // Nombre del PNG
            $pngName = pathinfo($pdfRelative, PATHINFO_FILENAME) . "-page-$index.png";
            $pngFullPath = $outputPath . '/' . $pngName;

            // Guardar PNG
            $page->writeImage($pngFullPath);

            $generated[] = $outputDir . '/' . $pngName;
            $pageIndex++;
        }

        return response()->json([
            'success' => true,
            'pages' => $generated,
            'total_pages' => count($generated)
        ]);
    }

    public function searchGroutBlock()
    {
        $groutBlock = DB::table('grout_block')
            ->select('id', 'name', 'grout')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $groutBlock
        ]);
    }

    public function listPlanImages(Request $request)
    {
        $projectId = $request->query('project');
        $prefix    = $request->query('prefix');

        if (!$projectId || !$prefix) {
            return response()->json([
                'error' => 'Missing parameters: project or prefix'
            ], 400);
        }

        $folderPath = public_path("uploads/projects/project{$projectId}/thumb");

        if (!File::exists($folderPath)) {
            return response()->json([]);
        }

        // Trae TODO lo que empiece con el prefix, sin asumir formato
        $files = glob($folderPath . '/' . $prefix . '*.png') ?: [];

        $rows = [];

        foreach ($files as $path) {
            $base = basename($path);

            // ✅ Tu regla de orden: siempre por "__P{n}" al final
            // Ej: "...__P1.png"
            $page = PHP_INT_MAX;
            if (preg_match('/__P(\d+)\.png$/i', $base, $m)) {
                $page = (int)$m[1];
            }

            $rows[] = [
                'page' => $page,
                'path' => $path,
            ];
        }

        // Orden numérico por página
        usort($rows, function ($a, $b) {
            return $a['page'] <=> $b['page'];
        });

        // Devuelve rutas relativas para React
        $result = array_map(function ($row) {
            return ltrim(str_replace(public_path(), '', $row['path']), '/\\');
        }, $rows);

        return response()->json($result);
    }

    private function sanitizeForFilename(string $s): string
    {
        $s = trim(preg_replace('/\s+/', ' ', $s));
        $s = preg_replace('/[\\\\\/:\*\?"<>\|]/', '-', $s);
        return trim($s);
    }

    private function projectDir(string $projectId): string
    {
        $pid = $this->sanitizeForFilename($projectId);
        return public_path("uploads/projects/project{$pid}");
    }

    private function ensureDir(string $dir): void
    {
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0777, true, true);
        }
    }

    private function getPageIndexFromFilename(string $filename): int
    {
        // Busca "__P12.png" al final
        if (preg_match('/__P(\d+)\.png$/i', $filename, $m)) {
            return (int)$m[1];
        }
        return PHP_INT_MAX;
    }

    // =========================================================
    // 1) ANALYZE: PDF completo -> GPT -> [{page, code, source}]
    // =========================================================
    public function analyzePdf(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf',
            'project_id' => 'nullable'
        ]);

        $pdf = $request->file('file');
        $pdfName = $pdf->getClientOriginalName() ?: 'document.pdf';
        $pdfBytes = file_get_contents($pdf->getRealPath());

        $prompt = <<<PROMPT
        Analiza el documento PDF proporcionado.

        Es un proyecto de planos de arquitectura.
        Cada página (lámina) tiene UN SOLO código que la identifica
        (número de plano o nombre de la lámina).

        Para CADA página:
        1. Identifica el código principal de la lámina.
        2. El código suele encontrarse en el title block.
        3. Elige SOLO UN código por página (el más representativo).
        4. Si hay varios textos similares, selecciona el más probable como identificador.
        5. Si no existe un código claro, devuelve null.
        6. No inventes códigos.
        7. No repitas códigos entre páginas.

        Devuelve ÚNICAMENTE un JSON con este formato exacto:

        [
        {
            "page": 1,
            "code": "A-101",
            "source": "text|ocr|mixed"
        }
        ]

        No incluyas explicaciones ni texto adicional.
        PROMPT;

        // OpenAI Chat Completions (con archivo no siempre soporta "input_file" aquí).
        // En Laravel lo más estable es usar OpenAI Responses API.
        // Si tu cuenta no soporta Responses con input_file por HTTP directo, te doy fallback más abajo.

        try {
            $apiKey = config('services.openai.key');
            if (!$apiKey) {
                return response()->json(['error' => 'OPENAI key no configurada'], 500);
            }

            // ============================================================
            // RESPONSES API (recomendado)
            // Enviamos el PDF como "base64" dentro del JSON.
            // ============================================================
            $pdfB64 = base64_encode($pdfBytes);
            $pdfDataUrl = "data:application/pdf;base64,{$pdfB64}";

            $payload = [
                'model' => 'gpt-5-nano',
                'input' => [
                    [
                    'role' => 'user',
                    'content' => [
                        [
                        'type' => 'input_file',
                        'filename' => $pdfName,
                        'file_data' => $pdfDataUrl, // ✅ ESTE ES EL BUENO
                        ],
                        [
                        'type' => 'input_text',
                        'text' => $prompt,
                        ],
                    ],
                    ],
                ],
            ];

            $resp = Http::withToken($apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/responses', $payload);

            if (!$resp->ok()) {
                return response()->json([
                    'error' => 'OpenAI error',
                    'status' => $resp->status(),
                    'body' => $resp->json(),
                ], 500);
            }

            $data = $resp->json();

            $outputText = $this->extractResponseText($data);

            if ($outputText === '') {
                return response()->json([
                    'error' => 'Respuesta IA vacía',
                    'raw' => $data,
                ], 500);
            }

            $maybeJson = trim($outputText);

            // 1) Si viene como string con comillas y escapes, quítale el envoltorio
            // Ej: "\"[{\\\"page\\\":1}]\""  o  "\"{\\\"page\\\":1}\""
            if (
                (str_starts_with($maybeJson, '"') && str_ends_with($maybeJson, '"')) ||
                (str_starts_with($maybeJson, "'") && str_ends_with($maybeJson, "'"))
            ) {
                $maybeJson = substr($maybeJson, 1, -1);
            }

            // 2) Des-escapar \" \n \t etc
            $maybeJson = stripcslashes($maybeJson);
            $maybeJson = trim($maybeJson);

            // 3) Si el modelo devolvió objetos sueltos "{...},{...}" sin []
            // o devolvió varios objetos pegados, envuélvelo como array
            if (!str_starts_with($maybeJson, '[')) {
                // Si parece una lista de objetos separados por "},{" o "}, {"
                if (str_contains($maybeJson, '},{') || str_contains($maybeJson, '}, {')) {
                    $maybeJson = '[' . $maybeJson . ']';
                }
            }

            // 4) Si viene con texto alrededor, extrae el primer bloque array [...]
            if (!preg_match('/\[[\s\S]*\]/', $maybeJson, $m)) {
                return response()->json([
                    'error' => 'No se encontró JSON en la respuesta',
                    'raw' => \Illuminate\Support\Str::limit($maybeJson, 2000),
                ], 500);
            }

            $maybeJson = $m[0];

            // 5) Decode final
            $parsed = json_decode($maybeJson, true);

            if (!is_array($parsed)) {
                return response()->json([
                    'error' => 'JSON inválido (no se pudo decodificar)',
                    'raw' => \Illuminate\Support\Str::limit($maybeJson, 2000),
                    'json_error' => json_last_error_msg(),
                ], 500);
            }

            // 3) Normaliza fila por fila (solo arrays)
            $out = [];

            foreach ($parsed as $row) {
                if (!is_array($row)) {
                    // Si viene algo raro, lo ignoramos
                    continue;
                }

                $page = isset($row['page']) ? (int)$row['page'] : null;

                $code = $row['code'] ?? null;
                if ($code !== null) {
                    $code = $this->sanitizeForFilename((string)$code);
                    if ($code === '') $code = null;
                }

                $source = $row['source'] ?? 'mixed';
                $source = $this->sanitizeForFilename((string)$source);
                if ($source === '') $source = 'mixed';

                $out[] = [
                    'page' => $page,
                    'code' => $code,
                    'source' => $source,
                ];
            }

            return response()->json($out);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Error analizando PDF',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function extractResponseText(array $data): string
    {
        // 1) Si viene output_text, úsalo
        if (!empty($data['output_text']) && is_string($data['output_text'])) {
            return trim($data['output_text']);
        }

        // 2) Busca el primer item type=message y concatena content[].text
        $out = '';

        if (!empty($data['output']) && is_array($data['output'])) {
            foreach ($data['output'] as $item) {
                if (!is_array($item)) continue;
                if (($item['type'] ?? '') !== 'message') continue;

                $content = $item['content'] ?? [];
                if (!is_array($content)) continue;

                foreach ($content as $c) {
                    if (!is_array($c)) continue;

                    // Normalmente viene: { type: "output_text", text: "..." }
                    if (!empty($c['text']) && is_string($c['text'])) {
                        $out .= $c['text'];
                    }

                    // Otros formatos posibles
                    if (!empty($c['type']) && $c['type'] === 'output_text' && !empty($c['text'])) {
                        $out .= $c['text'];
                    }
                }
            }
        }

        return trim($out);
    }

    // =========================================================
    // 2) UPLOAD: guarda PDF recortado + PNGs ya generados
    // =========================================================
    public function uploadPdfPngs(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'file' => 'required|file|mimes:pdf',
            'pngs' => 'nullable|array',
            'pngs.*' => 'file|mimes:png',
            'page_codes' => 'nullable|string',
        ]);

        $projectId = (string)$request->input('project_id');
        $dir = $this->projectDir($projectId);
        $this->ensureDir($dir);

        // Guarda PDF (recortado)
        $pdf = $request->file('file');
        $pdfName = $this->sanitizeForFilename($pdf->getClientOriginalName() ?: 'document.pdf');
        $pdf->move($dir, $pdfName);

        // Guarda PNGs (ya renombrados en frontend)
        $saved = [];
        if ($request->hasFile('pngs')) {
            foreach ($request->file('pngs') as $png) {
                $pngName = $this->sanitizeForFilename($png->getClientOriginalName() ?: uniqid('page_') . '.png');
                if (!Str::endsWith(Str::lower($pngName), '.png')) continue;

                $png->move($dir, $pngName);
                $saved[] = "uploads/projects/project{$this->sanitizeForFilename($projectId)}/{$pngName}";
            }
        }

        return response()->json([
            'ok' => true,
            'project_id' => $projectId,
            'pdf_saved_as' => "uploads/projects/project{$this->sanitizeForFilename($projectId)}/{$pdfName}",
            'pngs_saved' => $saved
        ]);
    }

    public function uploadPngChunk(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'pdf_name'   => 'required|string',
            'pages'      => 'required|string', // JSON: [1,2,3...]
            'pngs'       => 'required',
            'pngs.*'     => 'file|mimes:png|max:20480', // 20MB c/u (ajusta)
        ]);

        $projectId = (string)$request->project_id;

        $pdfName = $request->input('pdf_name');
        $pdfBase = preg_replace('/\.pdf$/i', '', $pdfName);
        $pdfBase = $this->sanitizeForFilename($pdfBase);

        $outDir = public_path("uploads/projects/project{$projectId}/thumb");
        if (!File::isDirectory($outDir)) {
            File::makeDirectory($outDir, 0777, true, true);
        }

        $saved = [];

        foreach ($request->file('pngs', []) as $file) {
            // Conserva el nombre que mandó el frontend (ya viene con código)
            $name = $this->sanitizeForFilename($file->getClientOriginalName());

            // seguridad: si por alguna razón viene vacío
            if ($name === '') {
                $name = $pdfBase . '__UNKNOWN__' . uniqid() . '.png';
            }

            $file->move($outDir, $name);
            $saved[] = "uploads/projects/project{$projectId}/thumb/{$name}";
        }

        return response()->json([
            'ok' => true,
            'saved' => $saved,
        ]);
    }

    public function fullExists(Request $request)
    {
        $projectId = $request->query('project_id');
        $file = $request->query('file');

        //dd('HIT fullExists');

        if (!$projectId || !$file) {
            return response()->json(['exists' => false], 400);
        }

        $safe = $this->sanitizeForFilename($file);
        $path = public_path("uploads/projects/project{$projectId}/full/{$safe}");

        return response()->json(['exists' => File::exists($path)]);
    }

    public function uploadFullPage(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'png'        => 'required|file|mimes:png|max:51200',
        ]);

        $projectId = (string)$request->project_id;

        $outDir = public_path("uploads/projects/project{$projectId}/full");
        if (!File::isDirectory($outDir)) {
            File::makeDirectory($outDir, 0777, true, true);
        }

        $file = $request->file('png');
        $name = $this->sanitizeForFilename($file->getClientOriginalName());

        $file->move($outDir, $name);

        return response()->json([
            'ok' => true,
            'path' => "uploads/projects/project{$projectId}/full/{$name}",
        ]);
    }

    // =========================================================
    // 3) LIST: lista PNGs existentes para FileView
    //     GET /api/list-plan-images?project=123&prefix=Plano
    // =========================================================
    /*public function listPlanImages(Request $request)
    {
        $project = (string)$request->query('project', '');
        $prefix = (string)$request->query('prefix', '');

        if ($project === '' || $prefix === '') {
            return response()->json(['error' => 'project y prefix son requeridos'], 400);
        }

        $dir = $this->projectDir($project);
        if (!File::isDirectory($dir)) {
            return response()->json([]);
        }

        $prefixSafe = $this->sanitizeForFilename($prefix);

        $files = collect(File::files($dir))
            ->filter(function ($f) use ($prefixSafe) {
                $name = $f->getFilename();
                return Str::endsWith(Str::lower($name), '.png') && Str::startsWith($name, $prefixSafe . '__');
            })
            ->map(fn($f) => $f->getFilename())
            ->sort(function ($a, $b) {
                return $this->getPageIndexFromFilename($a) <=> $this->getPageIndexFromFilename($b);
            })
            ->values()
            ->all();

        $pid = $this->sanitizeForFilename($project);

        $paths = array_map(function ($name) use ($pid) {
            return "uploads/projects/project{$pid}/{$name}";
        }, $files);

        return response()->json($paths);
    }*/

}

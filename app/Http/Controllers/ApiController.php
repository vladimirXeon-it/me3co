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
use App\Models\DrawingTemplate;
use App\Http\Controllers\WallController;
use App\Services\StorageService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PdfReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;   // si validas
use App\Models\CourseBand;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Html as HtmlReader;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
//use Illuminate\Support\Facades\Schema;

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
            $reports[] = [
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

        $day_required = $required_unit / $wall_material['production_rate'];

        $cost = $required_unit * ((float) $wall_material['prices'] + (float) $wall_material['cleaning_subed_out'] + (float) $wall_material['cleaning_cost'] + (float) $wall_material['production_subed_out_cost']);

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
            $cost = $counts['count'] * ((int) $material->prices + (int) $material->cleaning_subbed + (int) $material->cleaning_cost + (int) $material->production_subed_out_cost);
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
        $perimeter_cost = (int) $perimeter_material->prices * $perimeter_material_required;
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
            $required = (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['thickness'] / 12);
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
            $required = (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['quantity']);
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
            $required = (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['required']);
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
            $required = (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['thickness'] / 12);
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
            $required = (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['quantity']);
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
            $required = (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['required']);
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
        $perimeter_cost = (int) $perimeter_material->prices * $perimeter_material_required;
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
        $requestBody = $request->all();
        $used_materials = array_merge([$requestBody['meta']['main_material']], $requestBody['meta']['additional_material']);
        $materials = Material::where('user_id', Auth::id())->whereIn('name', $used_materials)->get();

        foreach ($materials as $material) {
            $cost = count($requestBody['path']) * ((int) $material->prices + (int) $material->cleaning_subbed + (int) $material->cleaning_cost + (int) $material->production_subed_out_cost);
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

    private function normalizarIdsProyecto($value): array
    {
        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = json_decode((string) $value, true);

            if (!is_array($ids)) {
                $ids = [];
            }
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            function ($id) {
                return $id > 0;
            }
        )));
    }

    public function workspace($projectId)
    {
        $userId = (int) auth()->id();

        $currentProject = Project::where('id', $projectId)
            ->where('user_id', $userId)
            ->first();

        if (!$currentProject) {
            return response()->json([
                'status' => 404,
                'message' => 'Project not found',
                'current_project' => null,
                'projects' => [],
                'equipments' => [],
                'materials' => [],
                'crews' => [],
            ], 404);
        }

        $equipmentIds = $this->normalizarIdsProyecto(
            $currentProject->equipments
        );

        $crewIds = $this->normalizarIdsProyecto(
            $currentProject->crews
        );

        $projects = Project::where('user_id', $userId)
            ->select(
                'id',
                'name',
                'bid_date',
                'created_at',
                'updated_at'
            )
            ->orderByDesc('created_at')
            ->get();

        $equipments = Equipment::where('user_id', $userId)
            ->where('project_id', $currentProject->id)
            ->orderBy('name')
            ->get();

        $materials = Material::where('user_id', $userId)
            ->where('project_id', $currentProject->id)
            ->orderBy('name')
            ->get();

        $crews = Crew::where('user_id', $userId)
            ->where('project_id', $currentProject->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 200,

            'current_project' => [
                'id' => $currentProject->id,
                'name' => $currentProject->name,
            ],

            'projects' => $projects,
            'equipments' => $equipments,
            'materials' => $materials,
            'crews' => $crews,
        ]);
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
        $material_divisions = MaterialDivision::orderBy('id', 'desc')->get()->load([
            'materials' => function ($query) {
                $query->where('user_id', Auth::id());
            }
        ]);
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

                // Importar pÃƒÆ’Ã‚Â¡gina
                $tpl = $pdf->importPage($pageNum);

                // Obtener tamaÃƒÆ’Ã‚Â±o de pÃƒÆ’Ã‚Â¡gina
                $size = $pdf->getTemplateSize($tpl);

                // Crear pÃƒÆ’Ã‚Â¡gina nueva con el tamaÃƒÆ’Ã‚Â±o correspondiente
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

                // AÃƒÆ’Ã‚Â±adir contenido
                $pdf->useTemplate($tpl);
            }
        }

        // Guardar PDF recortado
        $pdf->Output('F', $outputPdf);

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
            $directory = "projects/project{$project_id}";

            // Nombre final = nombre original del PDF
            $originalName = $file->getClientOriginalName();

            // sanitiza nombre para Windows/servidor
            $safeName = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]+/', '-', $originalName);

            // Guardar con el nuevo StorageService
            StorageService::putFileAs($directory, $file, $safeName, 'public');

            return response()->json([
                'status' => 200,
                'message' => "PDF recibido correctamente",
                'pdf_path' => StorageService::url($directory . '/' . $safeName),
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

        $directory = "projects/project{$id}";

        if (StorageService::useS3()) {
            $cleanDir = StorageService::cleanPath($directory);
            if (!Storage::disk('s3')->exists($cleanDir)) {
                return [];
            }
            // Obtenemos archivos del disco s3
            $files = Storage::disk('s3')->files($cleanDir);
            
            // Excluir directorios como thumb o full, scandir en local solo trae archivos directos de esa carpeta
            $response = [];
            foreach ($files as $file) {
                $filename = basename($file);
                // Si es un archivo directo (los subdirectorios en Flysystem se listan si son paths completos, pero files() solo trae archivos)
                $response[] = StorageService::url($file);
            }
        } else {
            // Ruta en public/
            $path = public_path("uploads/" . $directory);

            if (!is_dir($path)) {
                return [];
            }

            // Leer archivos reales
            $files = array_values(array_diff(scandir($path), ['.', '..']));

            // Convertir a rutas accesibles desde frontend
            $response = array_map(function ($file) use ($id) {
                return "uploads/projects/project{$id}/{$file}";
            }, $files);
        }

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

        // Asegurar que la ruta SIEMPRE sea: projects/project{id}
        $directory = "project" . $project->id;

        $relativePath = "projects/$directory";

        DB::beginTransaction();

        try {
            // ✅ 1. Eliminar archivos físicos en local o S3
            StorageService::deleteDirectory($relativePath);

            // ✅ 2. Eliminar WALLS por project_id + user_id
            Wall::where('project_id', $project->id)
                ->where('user_id', auth()->id())
                ->delete();

            // ✅ 3. Eliminar DRAWINGS por path
            DB::table('drawings')
                ->where('path', 'like', '%/project' . $project->id . '/%')
                ->delete();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'All Files Deleted!'
            ]);

        } catch (\Throwable $throwable) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Error deleting data',
                'error' => $throwable->getMessage()
            ]);
        }
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

        StorageService::delete($path);

        return \json_encode([
            'status' => 200
        ]);
    }
    public function delete_folder(Request $request)
    {
        $path = $request->post('path');
        $directory = Project::find($request->post('project_id'))->document->directory;
        $relativePath = "projects/$directory$path";
        StorageService::deleteDirectory($relativePath);

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
        $currentpath = $request->all();
        $fileLocation = $currentpath['fileLocation'];
        $windowLocation = $currentpath['windowLocation'];
        $user = User::find(Auth::id())->update([
            'current_location_file' => $fileLocation,
            'current_location' => $windowLocation,
        ]);
        return [
            'status' => true
        ];
    }
    public function sync_local_db(Request $request)
    {
        $data = $request->json()->all();
        $user = User::find(Auth::id())->update([
            'local_db' => $data
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
            $data = $request->json()->all();
            $document = Document::where('directory', $projectFolder)->update([
                'local_db' => $data
            ]);
        } else {
            $data = $request->json()->all();
            $project_id = str_replace("project", "", $projectFolder);
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
        $data = str_replace("project", "", $data);
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
        $data = $request->all();
        $templateName = $data['template_name'];
        $tradeName = json_decode($data['trade_name'])->name;
        $templateType = $data['template_type'];
        $isGlobal = isset($data['is_global']) ? (int) $data['is_global'] : 0;
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
            $lineTemplate->is_global = $isGlobal;
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

    private function generarIdUniqueWall($wall): string
    {
        $nombre_takeoff = trim((string) ($wall->name ?? ''));

        if ($nombre_takeoff === '') {
            $nombre_takeoff = 'takeoff';
        }

        return $wall->id . '_' . Str::slug($nombre_takeoff, '_');
    }

    private function asegurarIdUniqueWall($wall)
    {
        if (!$wall) {
            return null;
        }

        if (empty($wall->idUnique)) {
            $wall->idUnique = $this->generarIdUniqueWall($wall);
            $wall->save();
        }

        return $wall;
    }

    private function buscarWallPorIdUnique(int $userId, int $projectId, string $idUnique = '')
    {
        if ($idUnique === '') {
            return null;
        }

        $wall = Wall::select(
                    'id',
                    'idUnique'
                )
                ->where('user_id', $userId)
                ->where('project_id', $projectId)
                ->where('idUnique', $idUnique)
                ->first();

        if ($wall) {
            return $this->asegurarIdUniqueWall($wall);
        }

        return null;
    }

    /*public function CalculateData(Request $request)
    {
        try {
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
                ->where('project_id', $projectId)
                ->first();
            // dd($checkTemplate);

            // echo($data['formData']['additionalDatas']);
            // $data->additionalDatas = json_decode($data['formData']['additionalDatas']);
            $data['formData']['user_id'] = $userId;
            $data['formData']['project_id'] = $projectId;
            $data['formData']['type'] = $type;
            $data['formData']['formData'] = $formData;

            try {
                if (!isset($existing_wall->id)) {


                    $resultado =  Wall::create($data['formData']);
                } else {
                    $resultado =  $existing_wall->update($data['formData']);
                }
            } catch (QueryException $e) {
                // SQL + bindings (aquÃƒÆ’Ã‚Â­ estÃƒÆ’Ã‚Â¡ el error REAL)
                return response()->json([
                    'status' => 400,
                    'error' => 'QueryException during save',
                    'message' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                ], 400);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 400,
                    'error' => 'Throwable during save',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 400);
            }
            $existing_wall = Wall::where('user_id', $userId)
                ->where('name', $templateName)
                ->where('project_id', $projectId)
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
        } catch (\Throwable $e) {
            // Catch final (por si truena antes)
            return response()->json([
                'status' => 500,
                'error' => 'Unhandled exception',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }*/

    private function convertirMaterialesAString(array $formData): array
    {
        $campos_materiales = [
            'wall_material',
            'top_wall_material',
            'fill_material',
            'reinforcing_material',
            'jamb_material',
            'header_material',
            'other_fill',
            'trade'
        ];

        foreach ($campos_materiales as $campo_material) {
            if (isset($formData[$campo_material]) && is_array($formData[$campo_material])) {
                $formData[$campo_material] = json_encode($formData[$campo_material]);
            }

            if (isset($formData[$campo_material]) && is_object($formData[$campo_material])) {
                $formData[$campo_material] = json_encode($formData[$campo_material]);
            }
        }

        return $formData;
    }

    private function filtrarColumnasWall(array $data): array
    {
        static $columnasWall = null;

        if ($columnasWall === null) {
            $columnasWall = array_flip(Schema::getColumnListing('walls'));
        }

        return array_intersect_key($data, $columnasWall);
    }

    private function obtenerOpeningsVinculados(
        int $projectId,
        int $userId,
        string $affectedTakeoffIdUnique
    ): array {
        $affectedTakeoffIdUnique = trim($affectedTakeoffIdUnique);

        if ($projectId <= 0 || $affectedTakeoffIdUnique === '') {
            return [];
        }

        $drawings = DB::table('drawings')
            ->where('path', 'like', '%projects/project' . $projectId . '/%')
            ->select('id', 'counts')
            ->get();

        $openings = [];
        $seen = [];

        foreach ($drawings as $drawing) {
            $counts = $this->safeJson($drawing->counts ?? null, []);

            if (is_object($counts)) {
                $counts = (array) $counts;
            }

            if (is_array($counts)) {
                $this->buscarOpeningsEnCounts(
                    $counts,
                    $affectedTakeoffIdUnique,
                    (int) $drawing->id,
                    $openings,
                    $seen
                );
            }
        }

        return array_values($openings);
    }

    private function buscarOpeningsEnCounts(
        array $node,
        string $affectedTakeoffIdUnique,
        int $drawingId,
        array &$openings,
        array &$seen
    ): void {
        $opening = $this->normalizarOpeningDeCount($node);
        $linkedId = $this->extraerAffectedTakeoffIdUnique($opening);

        if ($linkedId !== '') {
            if ($linkedId === $affectedTakeoffIdUnique) {
                $opening['_drawing_id'] = $drawingId;
                $openingKey = trim((string) (
                    $opening['id_unique']
                    ?? $opening['idUnique']
                    ?? $opening['opening_id']
                    ?? $opening['id']
                    ?? ''
                ));

                if ($openingKey === '') {
                    $openingKey = md5(json_encode(
                        [$drawingId, $opening],
                        JSON_UNESCAPED_UNICODE
                    ));
                }

                $uniqueKey = $drawingId . '|' . $openingKey;

                if (!isset($seen[$uniqueKey])) {
                    $seen[$uniqueKey] = true;
                    $openings[] = $opening;
                }
            }

            return;
        }

        foreach ($node as $child) {
            if (is_object($child)) {
                $child = (array) $child;
            }

            if (is_array($child)) {
                $this->buscarOpeningsEnCounts(
                    $child,
                    $affectedTakeoffIdUnique,
                    $drawingId,
                    $openings,
                    $seen
                );
            }
        }
    }

    private function normalizarOpeningDeCount(array $item): array
    {
        $opening = [];

        foreach (['opening_template', 'template', 'opening', 'meta'] as $key) {
            if (!isset($item[$key])) {
                continue;
            }

            $value = $item[$key];

            if (is_string($value)) {
                $value = $this->safeJson($value, []);
            }

            if (is_object($value)) {
                $value = (array) $value;
            }

            if (is_array($value)) {
                $opening = array_replace($opening, $value);
            }
        }

        return array_replace($opening, $item);
    }

    private function extraerAffectedTakeoffIdUnique(array $opening): string
    {
        return trim((string) (
            $opening['affected_takeoff_id_unique']
            ?? $opening['affectedTakeoffIdUnique']
            ?? $opening['affected_takeoff_idUnique']
            ?? ''
        ));
    }

    public function CalculateData(Request $request)
    {
        try {
            $procesado = false;
            $data = $request->all();

            $projectId = (int) ($data['projectId'] ?? 0);
            $templateName = trim((string) ($data['template_name'] ?? ''));
            $type = ($data['type'] ?? "length") ?: "length";
            $user = auth()->user();
            $userId = (int) $user->id;

            $formDataLimpia = $data['formData'] ?? [];

            if (!is_array($formDataLimpia)) {
                $formDataLimpia = [];
            }

            /*
            * Evita guardar nuevamente un formData dentro de otro formData.
            */
            unset($formDataLimpia['formData']);

            /*
            * Son resultados generados por el cálculo.
            * No deben volver a guardarse ni enviarse al siguiente cálculo.
            */
            unset(
                $formDataLimpia['totales_html'],
                $formDataLimpia['totalsDatas'],
                $formDataLimpia['materialesAgrupados'],
                $formDataLimpia['project'],
                $formDataLimpia['linked_openings'],
                $formDataLimpia['openings_applied']
            );

            $idUnique = trim((string) ($formDataLimpia['idUnique'] ?? ''));

            // SOLO busca por idUnique
            $existing_wall = $this->buscarWallPorIdUnique(
                $userId,
                $projectId,
                $idUnique
            );

            $formDataLimpia['user_id'] = $userId;
            $formDataLimpia['project_id'] = $projectId;
            $formDataLimpia['type'] = $type;

            $formDataLimpia = $this->convertirMaterialesAString(
                $formDataLimpia
            );

            try {
                if (!$existing_wall) {
                    /*
                    * Primera creación.
                    * Guardamos una sola copia limpia dentro de la columna formData.
                    */
                    $datosGuardar = $this->filtrarColumnasWall($formDataLimpia);

                    $datosGuardar['formData'] = json_encode(
                        $formDataLimpia,
                        JSON_UNESCAPED_UNICODE
                    );

                    $nuevoWall = Wall::create($datosGuardar);

                    $nuevoWall->idUnique = $this->generarIdUniqueWall($nuevoWall);

                    /*
                    * Ahora que existe el ID, actualizamos también el JSON.
                    */
                    $formDataLimpia['idUnique'] = $nuevoWall->idUnique;

                    $nuevoWall->formData = json_encode(
                        $formDataLimpia,
                        JSON_UNESCAPED_UNICODE
                    );

                    $nuevoWall->save();

                    $existing_wall = $nuevoWall;
                } else {
                    $formDataLimpia['idUnique'] = $existing_wall->idUnique;

                    $datosGuardar = $this->filtrarColumnasWall($formDataLimpia);

                    $datosGuardar['formData'] = json_encode(
                        $formDataLimpia,
                        JSON_UNESCAPED_UNICODE
                    );

                    $existing_wall->update($datosGuardar);
                    //$existing_wall->refresh();
                }
            } catch (QueryException $e) {
                return response()->json([
                    'status' => 400,
                    'error' => 'QueryException during save',
                    'message' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                ], 400);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 400,
                    'error' => 'Throwable during save',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 400);
            }

            /*
             * Las aberturas se agregan sólo al cálculo actual. No se guardan
             * dentro del formData del wall para evitar copias obsoletas.
             */
            if ($type !== 'opening') {
                $formDataLimpia['linked_openings'] =
                    $this->obtenerOpeningsVinculados(
                        $projectId,
                        $userId,
                        (string) $existing_wall->idUnique
                    );
            }

            $wall_para_calculo = (object) [
                'id' => $existing_wall->id,
                'user_id' => $userId,
                'project_id' => $projectId,
                'type' => $type,
                'name' => $formDataLimpia['name'] ?? $templateName,
                'idUnique' => $existing_wall->idUnique,
                'formData' => json_encode(
                    $formDataLimpia,
                    JSON_UNESCAPED_UNICODE
                ),
            ];

            $wc = new WallController();
            $status = 200;

            if (isset($existing_wall->id)) {
                $resultado = $wc->recalculate($existing_wall->id, $wall_para_calculo);

                try {
                    $resultado->idUnique = $existing_wall->idUnique;
                    $procesado = true;
                } catch (\Throwable $th) {
                    $status = 400;
                    $resultado = $th->getMessage();
                }
            } else {
                $resultado = (object) $formDataLimpia;
            }

            return json_encode([
                'status' => $status,
                'resultado' => $resultado,
                'procesado' => $procesado,
                'templateName' => $templateName,
                'idUnique' => $existing_wall->idUnique ?? '',
                'userId' => $userId,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Unhandled exception',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /*public function ObtenData(Request $request)
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
            ->where('project_id', $projectId)
            ->orderByDesc('id')
            ->first();
        // dd($checkTemplate);

        // echo($data['formData']['additionalDatas']);
        // $data->additionalDatas = json_decode($data['formData']['additionalDatas']);
        $data['formData']['user_id'] = $userId;
        $data['formData']['project_id'] = $projectId;

        $existing_wall = Wall::where('user_id', $userId)
            ->where('name', $tradeName)
            ->where('project_id', $projectId)
            ->orderByDesc('id')
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
    }*/
    public function ObtenData(Request $request)
    {
        $data = $request->all();

        $projectId = (int) ($data['projectId'] ?? 0);
        $templateName = trim((string) ($data['template_name'] ?? ''));
        $idUnique = trim((string) ($data['idUnique'] ?? ''));

        $user = auth()->user();
        $userId = (int) $user->id;

        $existing_wall = $this->buscarWallPorIdUnique(
            $userId,
            $projectId,
            $idUnique
        );

        $wc = new WallController();
        $status = 200;

        if (isset($existing_wall->id)) {
            $resultado = $wc->recalculate($existing_wall->id);

            try {
                $resultado->idUnique = $existing_wall->idUnique;
            } catch (\Throwable $th) {
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
            'idUnique' => $existing_wall->idUnique ?? $idUnique,
            'userId' => $userId,
        ]);
    }
    public function getLineTemplate(Request $request)
    {
        $type = $request->query('type_template');
        $user = auth()->user();
        $userId = $user->id;
        $query = LineTemplate::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_global', 1);
        });
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

        // Asegurar formato de fecha
        if (isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s', strtotime($data['updated_at']));
        } else {
            $data['updated_at'] = now();
        }

        $id = $data['id'];
        $row = \DB::table($table)->where('id', $id)->first();

        // Si no existe por ID, pero la tabla es drawings e tiene path, buscamos por path
        if (!$row && $table === 'drawings' && !empty($data['path'])) {
            $rowByPath = \DB::table($table)->where('path', $data['path'])->first();
            if ($rowByPath) {
                // Si existe por path, actualizamos el ID al nuevo ID coordinado por el cliente
                $row = $rowByPath;
            }
        }

        if ($row) {
            if (strtotime($data['updated_at']) > strtotime($row->updated_at) || $row->id != $id) {
                \DB::table($table)->where('id', $row->id)->update($data);
                return response()->json(['status' => 'updated', 'previous_id' => $row->id, 'new_id' => $id]);
            } else {
                return response()->json(['status' => 'skipped', 'reason' => 'Registro mas reciente en servidor']);
            }
        } else {
            \DB::table($table)->insert($data);
            return response()->json(['status' => 'inserted']);
        }
    }

    public function pullUpdates(Request $request, $table)
    {
        $lastUpdated = $request->input('last_updated_at');
        $projectId = $request->input('project_id');

        $records = DB::table($table)
            ->when($projectId && $table === 'drawings', function ($query) use ($projectId) {
                $query->where('path', 'like', "/projects/project{$projectId}/%");
            })
            ->when($projectId && $table !== 'drawings', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->when($lastUpdated, function ($query) use ($lastUpdated) {
                $query->where(
                    'updated_at',
                    '>',
                    date('Y-m-d H:i:s', strtotime($lastUpdated))
                );
            })
            ->get();

        return response()->json([
            'status' => 200,
            'records' => $records,
        ]);
    }

    public function api_course_bands_index(Request $request)
    {
        $usuario = auth()->user();

        $q = DB::table('course_band')
            ->select('id', 'id_local', 'id_user', 'data', 'is_global', 'created_at', 'updated_at');

        /*if ($request->filled('id_user')) {
            $q->where('id_user', (int) $request->query('id_user'));
        }*/
        $q->where(function ($query) use ($usuario) {
            $query->where('id_user', $usuario->id)
                ->orWhere('is_global', 1);
        });

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
        $items = Arr::get($payload, 'items');
        $userId = auth()->id();
        $now = Carbon::now();
        $usuario = auth()->user();
        $is_global = ((int) $usuario->role === 1) ? 1 : 0;

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
            $data = Arr::get($row, 'data', []);

            // si data llega como string JSON, decodifica; si es array, lo dejamos
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $decoded;
                }
            }

            // serializa data a JSON para guardarlo
            $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

            // Ãƒâ€šÃ‚Â¿ya existe por (id_user, id_local)?
            $existing = DB::table('course_band')
                ->select('id')
                ->where('id_user', $userId)
                ->where('id_local', $idLocal)
                ->first();

            if ($existing) {
                // UPDATE (con CAST a JSON para columnas JSON)
                DB::statement(
                    "UPDATE course_band
                    SET data = CAST(? AS JSON), updated_at = ?, is_global = ?
                    WHERE id = ? AND id_user = ?",
                    [$dataJson, $now, $is_global, $existing->id, $userId]
                );

                $results['updated']++;
                $results['items'][] = [
                    'id' => $existing->id,
                    'id_user' => $userId,
                    'id_local' => $idLocal,
                ];
            } else {
                // INSERT
                DB::statement(
                    "INSERT INTO course_band (id_user, id_local, data, created_at, updated_at, is_global)
                    VALUES (?, ?, CAST(? AS JSON), ?, ?, ?)",
                    [$userId, $idLocal, $dataJson, $now, $now, $is_global]
                );

                // recupera el id insertado
                $newId = DB::getPdo()->lastInsertId();

                $results['inserted']++;
                $results['items'][] = [
                    'id' => (int) $newId,
                    'id_user' => $userId,
                    'id_local' => $idLocal,
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'message' => 'Upsert completado',
            'result' => $results
        ]);
    }

    /**
     * PUT /api/course-bands/{id}
     * Update directo por ID real de DB (ÃƒÆ’Ã‚Âºtil si ya lo tienes en el front).
     */
    public function api_course_bands_update(Request $request, $id)
    {
        $userId = auth()->id();
        $now = Carbon::now();
        $usuario = auth()->user();
        $is_global = ((int) $usuario->role === 1) ? 1 : 0;

        // validar existencia y propiedad del registro
        $row = DB::table('course_band')
            ->select('id', 'id_local')
            ->where('id', $id)
            ->where('id_user', $userId)
            ->first();

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'No encontrado'], 404);
        }

        $data = Arr::get($request->all(), 'data', []);
        $idLocal = Arr::get($request->all(), 'id_local'); // opcional

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            }
        }
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        // si mandan id_local nuevo, ÃƒÆ’Ã‚Âºsalo; si no, conserva el actual
        $idLocalNew = $idLocal ? (string) $idLocal : $row->id_local;

        // OJO: si cambias id_local, asegÃƒÆ’Ã‚Âºrate de no romper el UNIQUE (id_user,id_local)
        DB::statement(
            "UPDATE course_band
            SET id_local = ?, data = CAST(? AS JSON), updated_at = ?, is_global = ?
            WHERE id = ? AND id_user = ?",
            [$idLocalNew, $dataJson, $now, $is_global, $row->id, $userId]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Actualizado',
            'item' => [
                'id' => (int) $row->id,
                'id_user' => $userId,
                'id_local' => $idLocalNew,
            ]
        ]);
    }

    public function api_course_bands_show($id)
    {
        $userId = auth()->id();
        $usuario = auth()->user();

        $row = DB::table('course_band')
            ->select('id', 'id_local', 'id_user', 'data', 'created_at', 'updated_at')
            ->where('id', (int) $id)
            ->where(function ($query) use ($usuario) {
                $query->where('id_user', $usuario->id)
                    ->orWhere('is_global', 1);
            })
            ->first();

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'No encontrado'], 404);
        }

        return response()->json($row);
    }

    /**
     * DELETE /api/course-bands/{id}
     * Elimina 1 registro por ID (solo si pertenece al usuario logueado).
     */
    public function api_course_bands_destroy($id)
    {
        $userId = auth()->id();

        $row = DB::table('course_band')
            ->select('id')
            ->where('id', (int) $id)
            ->where('id_user', $userId)
            ->first();

        if (!$row) {
            return response()->json(['ok' => false, 'message' => 'No encontrado'], 404);
        }

        DB::table('course_band')
            ->where('id', (int) $id)
            ->where('id_user', $userId)
            ->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Eliminado',
            'deleted_id' => (int) $id,
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
        $fileName = $request->file_name;

        $directory = "projects/project{$projectId}";

        // Guardar PNG final con el nuevo StorageService
        StorageService::putFileAs($directory, $request->file('image'), $fileName, 'public');

        return response()->json([
            "status" => 200,
            "message" => "PNG guardada correctamente",
            "path" => StorageService::url($directory . '/' . $fileName)
        ]);
    }

    public function convert(Request $request)
    {
        $pdfRelative = $request->input('pdf');   // ejemplo: projects/myproject/file.pdf o uploads/projects/...
        $outputDir = dirname(StorageService::cleanPath($pdfRelative));    // carpeta donde dejaremos los PNG, ej: projects/project123

        $useS3 = StorageService::useS3();
        $pdfPath = '';

        if ($useS3) {
            $cleanPdfPath = StorageService::cleanPath($pdfRelative);
            if (!Storage::disk('s3')->exists($cleanPdfPath)) {
                return response()->json(['error' => 'PDF not found in S3'], 404);
            }
            // Descargar temporalmente para Imagick
            $pdfPath = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($pdfPath, Storage::disk('s3')->get($cleanPdfPath));
        } else {
            $pdfPath = public_path('uploads/' . StorageService::cleanPath($pdfRelative));
            if (!file_exists($pdfPath)) {
                return response()->json(['error' => 'PDF not found'], 404);
            }
        }

        // Cargar PDF completo
        $imagick = new \Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage($pdfPath);

        $pages = $imagick->getNumberImages();
        $pageIndex = 0;
        $generated = [];

        // Directorio temporal de salida para los PNGs si es S3
        $localOutputDir = $useS3 ? (sys_get_temp_dir() . '/pngs_' . uniqid()) : public_path('uploads/' . $outputDir);
        if (!file_exists($localOutputDir)) {
            mkdir($localOutputDir, 0777, true);
        }

        foreach ($imagick as $index => $page) {
            $page->setImageFormat('png');

            // Nombre del PNG
            $pngName = pathinfo($pdfRelative, PATHINFO_FILENAME) . "-page-$index.png";
            $pngFullPath = $localOutputDir . '/' . $pngName;

            // Guardar PNG localmente
            $page->writeImage($pngFullPath);

            if ($useS3) {
                // Subir a S3
                $s3Path = $outputDir . '/' . $pngName;
                Storage::disk('s3')->put($s3Path, file_get_contents($pngFullPath), 'public');
                $generated[] = StorageService::url($s3Path);
                // Eliminar archivo PNG local temporal
                @unlink($pngFullPath);
            } else {
                $generated[] = $outputDir . '/' . $pngName;
            }
            $pageIndex++;
        }

        // Limpiar archivos temporales
        $imagick->clear();
        $imagick->destroy();

        if ($useS3) {
            @unlink($pdfPath); // Eliminar PDF temporal
            @rmdir($localOutputDir); // Eliminar carpeta temporal de PNGs
        }

        return response()->json([
            'success' => true,
            'pages' => $generated,
            'total_pages' => count($generated)
        ]);
    }

    public function searchGroutBlock()
    {
        $groutBlock = Cache::remember('grout_block_list', 86400, function () {
            return DB::table('grout_block')
                ->select('id', 'name', 'grout')
                ->orderBy('id')
                ->get();
        });

        return response()->json([
            'status' => 200,
            'data' => $groutBlock
        ]);
    }

    private function normalizarNombrePlano(string $nombre): string
    {
        // Laravel ya decodifica normalmente el query,
        // pero esto cubre valores que todavía lleguen codificados.
        $nombre = rawurldecode($nombre);

        // Espacios especiales/invisibles.
        $nombre = str_replace(
            ["\xC2\xA0", "\xE2\x80\x8B", "\r", "\n", "\t"],
            ' ',
            $nombre
        );

        // Convierte espacios, guiones y guiones bajos en un solo espacio.
        $nombre = preg_replace('/[\s_-]+/u', ' ', $nombre);

        // Elimina cualquier otro carácter separador.
        $nombre = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $nombre);

        return mb_strtolower(
            trim(preg_replace('/\s+/u', ' ', $nombre)),
            'UTF-8'
        );
    }

    public function listPlanImages(Request $request)
    {
        $projectId = trim((string) $request->query('project', ''));
        $prefixOriginal = trim((string) $request->query('prefix', ''));

        if ($projectId === '' || $prefixOriginal === '') {
            return response()->json([
                'error' => 'Missing parameters: project or prefix'
            ], 400);
        }

        $prefixNormalizado = $this->normalizarNombrePlano(
            $prefixOriginal
        );

        $files = [];

        if (StorageService::useS3()) {
            $cleanDir = "projects/project{$projectId}/thumb";

            $allFiles = Storage::disk('s3')->files($cleanDir);

            foreach ($allFiles as $filePath) {
                $filename = basename($filePath);

                if (!preg_match('/\.(png|webp)$/i', $filename)) {
                    continue;
                }

                /*
                * Quita la parte correspondiente a la página.
                *
                * Ejemplo:
                * Cocke Cty Hwy Combined Drawings__PAGE-36__P14.webp
                *
                * Resultado:
                * Cocke Cty Hwy Combined Drawings
                */
                $nombreSinExtension = pathinfo(
                    $filename,
                    PATHINFO_FILENAME
                );

                $partes = preg_split(
                    '/__PAGE[-_]/i',
                    $nombreSinExtension,
                    2
                );

                $nombrePlanoArchivo = $partes[0] ?? $nombreSinExtension;

                $nombreArchivoNormalizado =
                    $this->normalizarNombrePlano($nombrePlanoArchivo);

                if ($nombreArchivoNormalizado === $prefixNormalizado) {
                    $files[] = $filePath;
                }
            }
        } else {
            $folderPath = public_path(
                "uploads/projects/project{$projectId}/thumb"
            );

            if (File::exists($folderPath)) {
                $localFiles = array_merge(
                    glob($folderPath . '/*.png') ?: [],
                    glob($folderPath . '/*.webp') ?: []
                );

                foreach ($localFiles as $path) {
                    $filename = basename($path);

                    $nombreSinExtension = pathinfo(
                        $filename,
                        PATHINFO_FILENAME
                    );

                    $partes = preg_split(
                        '/__PAGE[-_]/i',
                        $nombreSinExtension,
                        2
                    );

                    $nombrePlanoArchivo =
                        $partes[0] ?? $nombreSinExtension;

                    $nombreArchivoNormalizado =
                        $this->normalizarNombrePlano($nombrePlanoArchivo);

                    if ($nombreArchivoNormalizado === $prefixNormalizado) {
                        $files[] =
                            "projects/project{$projectId}/thumb/" .
                            $filename;
                    }
                }
            }
        }

        $rows = [];

        foreach ($files as $relativePath) {
            $base = basename($relativePath);

            $page = PHP_INT_MAX;

            /*
            * Tus archivos tienen formatos como:
            * __PAGE-36__P14.webp
            */
            if (preg_match(
                '/__P(\d+)\.(png|webp)$/i',
                $base,
                $matches
            )) {
                $page = (int) $matches[1];
            }

            $rows[] = [
                'page' => $page,
                'path' => $relativePath,
            ];
        }

        usort($rows, function ($a, $b) {
            return $a['page'] <=> $b['page'];
        });

        $result = array_map(function ($row) {
            if (StorageService::useS3()) {
                return StorageService::url($row['path']);
            }

            return 'uploads/' . $row['path'];
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
            return (int) $m[1];
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
        Cada pÃƒÆ’Ã‚Â¡gina (lÃƒÆ’Ã‚Â¡mina) tiene UN SOLO cÃƒÆ’Ã‚Â³digo que la identifica
        (nÃƒÆ’Ã‚Âºmero de plano o nombre de la lÃƒÆ’Ã‚Â¡mina).

        Para CADA pÃƒÆ’Ã‚Â¡gina:
        1. Identifica el cÃƒÆ’Ã‚Â³digo principal de la lÃƒÆ’Ã‚Â¡mina.
        2. El cÃƒÆ’Ã‚Â³digo suele encontrarse en el title block.
        3. Elige SOLO UN cÃƒÆ’Ã‚Â³digo por pÃƒÆ’Ã‚Â¡gina (el mÃƒÆ’Ã‚Â¡s representativo).
        4. Si hay varios textos similares, selecciona el mÃƒÆ’Ã‚Â¡s probable como identificador.
        5. Si no existe un cÃƒÆ’Ã‚Â³digo claro, devuelve null.
        6. No inventes cÃƒÆ’Ã‚Â³digos.
        7. No repitas cÃƒÆ’Ã‚Â³digos entre pÃƒÆ’Ã‚Â¡ginas.

        Devuelve ÃƒÆ’Ã…Â¡NICAMENTE un JSON con este formato exacto:

        [
        {
            "page": 1,
            "code": "A-101",
            "source": "text|ocr|mixed"
        }
        ]

        No incluyas explicaciones ni texto adicional.
        PROMPT;

        // OpenAI Chat Completions (con archivo no siempre soporta "input_file" aquÃƒÆ’Ã‚Â­).
        // En Laravel lo mÃƒÆ’Ã‚Â¡s estable es usar OpenAI Responses API.
        // Si tu cuenta no soporta Responses con input_file por HTTP directo, te doy fallback mÃƒÆ’Ã‚Â¡s abajo.

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
                                'file_data' => $pdfDataUrl, // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ ESTE ES EL BUENO
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
                    'error' => 'Respuesta IA vacÃƒÆ’Ã‚Â­a',
                    'raw' => $data,
                ], 500);
            }

            $maybeJson = trim($outputText);

            // 1) Si viene como string con comillas y escapes, quÃƒÆ’Ã‚Â­tale el envoltorio
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

            // 3) Si el modelo devolviÃƒÆ’Ã‚Â³ objetos sueltos "{...},{...}" sin []
            // o devolviÃƒÆ’Ã‚Â³ varios objetos pegados, envuÃƒÆ’Ã‚Â©lvelo como array
            if (!str_starts_with($maybeJson, '[')) {
                // Si parece una lista de objetos separados por "},{" o "}, {"
                if (str_contains($maybeJson, '},{') || str_contains($maybeJson, '}, {')) {
                    $maybeJson = '[' . $maybeJson . ']';
                }
            }

            // 4) Si viene con texto alrededor, extrae el primer bloque array [...]
            if (!preg_match('/\[[\s\S]*\]/', $maybeJson, $m)) {
                return response()->json([
                    'error' => 'No se encontrÃƒÆ’Ã‚Â³ JSON en la respuesta',
                    'raw' => \Illuminate\Support\Str::limit($maybeJson, 2000),
                ], 500);
            }

            $maybeJson = $m[0];

            // 5) Decode final
            $parsed = json_decode($maybeJson, true);

            if (!is_array($parsed)) {
                return response()->json([
                    'error' => 'JSON invÃƒÆ’Ã‚Â¡lido (no se pudo decodificar)',
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

                $page = isset($row['page']) ? (int) $row['page'] : null;

                $code = $row['code'] ?? null;
                if ($code !== null) {
                    $code = $this->sanitizeForFilename((string) $code);
                    if ($code === '')
                        $code = null;
                }

                $source = $row['source'] ?? 'mixed';
                $source = $this->sanitizeForFilename((string) $source);
                if ($source === '')
                    $source = 'mixed';

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
        // 1) Si viene output_text, ÃƒÆ’Ã‚Âºsalo
        if (!empty($data['output_text']) && is_string($data['output_text'])) {
            return trim($data['output_text']);
        }

        // 2) Busca el primer item type=message y concatena content[].text
        $out = '';

        if (!empty($data['output']) && is_array($data['output'])) {
            foreach ($data['output'] as $item) {
                if (!is_array($item))
                    continue;
                if (($item['type'] ?? '') !== 'message')
                    continue;

                $content = $item['content'] ?? [];
                if (!is_array($content))
                    continue;

                foreach ($content as $c) {
                    if (!is_array($c))
                        continue;

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
        /*$request->validate([
            'project_id' => 'required',
            'file' => 'required|file|mimes:pdf',
            'pngs' => 'nullable|array',
            'pngs.*' => 'file|mimes:png',
            'page_codes' => 'nullable|string',
        ]);*/

        $request->validate([
            'project_id' => 'required',
            'file' => 'required|file|mimes:pdf|max:102400',
            'pngs' => 'nullable|array',
            'pngs.*' => 'nullable|file|mimes:png|max:102400',
            'page_codes' => 'nullable|string',
        ]);

        $projectId = (string) $request->input('project_id');
        $directory = "projects/project" . $this->sanitizeForFilename($projectId);

        // Guarda PDF (recortado)
        $pdf = $request->file('file');
        $pdfName = $this->sanitizeForFilename($pdf->getClientOriginalName() ?: 'document.pdf');
        StorageService::putFileAs($directory, $pdf, $pdfName, 'public');

        // Guarda PNGs (ya renombrados en frontend)
        $saved = [];
        if ($request->hasFile('pngs')) {
            foreach ($request->file('pngs') as $png) {
                $pngName = $this->sanitizeForFilename($png->getClientOriginalName() ?: uniqid('page_') . '.png');
                if (!Str::endsWith(Str::lower($pngName), '.png'))
                    continue;

                StorageService::putFileAs($directory, $png, $pngName, 'public');
                $saved[] = StorageService::url($directory . '/' . $pngName);
            }
        }

        return response()->json([
            'ok' => true,
            'project_id' => $projectId,
            'pdf_saved_as' => StorageService::url($directory . '/' . $pdfName),
            'pngs_saved' => $saved
        ]);
    }

    public function uploadPngChunk(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'pdf_name' => 'required|string',
            'pages' => 'required|string', // JSON: [1,2,3...]
            'pngs' => 'required',
            'pngs.*' => 'file|mimetypes:image/webp|max:20480', // 20MB c/u (ajusta)
        ]);

        $projectId = (string) $request->project_id;

        $pdfName = $request->input('pdf_name');
        $pdfBase = preg_replace('/\.pdf$/i', '', $pdfName);
        $pdfBase = $this->sanitizeForFilename($pdfBase);

        $outDir = "projects/project{$projectId}/thumb";

        $saved = [];

        foreach ($request->file('pngs', []) as $file) {
            // Conserva el nombre que mandó el frontend (ya viene con código)
            $name = $this->sanitizeForFilename($file->getClientOriginalName());

            // seguridad: si por alguna razón viene vacío
            if ($name === '') {
                $name = $pdfBase . '__UNKNOWN__' . uniqid() . '.png';
            }

            StorageService::putFileAs($outDir, $file, $name, 'public');
            $saved[] = StorageService::url($outDir . '/' . $name);
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
        $safePng = preg_replace('/\.(webp|jpg|jpeg)$/i', '.png', $safe);
        $path = "projects/project{$projectId}/full/{$safePng}";

        return response()->json([
            'exists' => StorageService::exists($path),
            'url' => StorageService::exists($path)
                ? StorageService::url($path)
                : null,
        ]);
    }

    public function uploadFullPage(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'png' => 'required|file|mimes:png|max:51200',
        ]);

        $projectId = (string) $request->project_id;

        $outDir = "projects/project{$projectId}/full";

        $file = $request->file('png');
        $name = $this->sanitizeForFilename($file->getClientOriginalName());

        StorageService::putFileAs($outDir, $file, $name, 'public');

        return response()->json([
            'ok' => true,
            'path' => "{$outDir}/{$name}",
            'url' => StorageService::url("{$outDir}/{$name}"),
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

    public function runQuery(Request $request)
    {
        $sql = trim((string) $request->input('query', ''));

        if ($sql === '') {
            return response()->json([
                'ok' => false,
                'error' => 'query is required',
            ], 422);
        }

        // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ Solo SELECT (tambiÃƒÆ’Ã‚Â©n soporta WITH ... SELECT)
        $normalized = strtoupper(preg_replace('/\s+/', ' ', $sql));

        $startsOk = str_starts_with($normalized, 'SELECT ')
            || str_starts_with($normalized, 'WITH ');

        if (!$startsOk) {
            return response()->json([
                'ok' => false,
                'error' => 'Only SELECT queries are allowed.',
            ], 403);
        }

        // ÃƒÂ¢Ã‚ÂÃ…â€™ Bloqueos rÃƒÆ’Ã‚Â¡pidos (evita multi statements y comentarios)
        if (str_contains($sql, ';') || str_contains($sql, '--') || str_contains($sql, '/*') || str_contains($sql, '*/')) {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid characters detected.',
            ], 403);
        }

        // ÃƒÂ¢Ã‚ÂÃ…â€™ Keywords prohibidas
        $forbidden = ['DELETE ', 'DROP ', 'TRUNCATE ', 'GRANT ', 'REVOKE '];
        foreach ($forbidden as $kw) {
            if (str_contains($normalized, $kw)) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Forbidden keyword detected.',
                ], 403);
            }
        }

        // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ params opcionales (recomendado)
        $params = $request->input('params', []);
        if (!is_array($params))
            $params = [];

        // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ lÃƒÆ’Ã‚Â­mite de filas (si no trae LIMIT, lo agregamos)
        $maxRows = 500;
        if (!preg_match('/\bLIMIT\b/i', $sql)) {
            $sql .= ' LIMIT ' . $maxRows;
        }

        try {
            $rows = DB::select($sql, $params);

            return response()->json([
                'ok' => true,
                'count' => count($rows),
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Query failed',
                'message' => $e->getMessage(), // en producciÃƒÆ’Ã‚Â³n puedes ocultarlo
            ], 500);
        }
    }

    public function bulk(Request $request)
    {
        $path = trim((string) $request->input('path', ''));
        $projectId = trim((string) $request->input('projectId', ''));

        if ($path === '/' && $projectId !== '') {
            $path = '/projects/project' . $projectId . '/';
        }

        $basePath = $this->obtenerPathProyecto($path);
        $projectId = trim((string) $request->input('projectId', ''));

        if ($basePath === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Missing path',
                'reports' => [],
            ], 422);
        }

        $drawings = DB::table('drawings')
            ->where('path', 'like', '%' . $basePath . '%')
            ->get();

        if ($drawings->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'No drawings found for this path',
                'reports' => [],
            ], 404);
        }

        $lines = [];
        $areas = [];
        $perimeters = [];

        foreach ($drawings as $drawing) {
            $drawing_lines = $this->safeJson($drawing->line ?? null, []);
            $drawing_areas = $this->safeJson($drawing->area ?? null, []);
            $drawing_perimeters = $this->safeJson($drawing->perimeter ?? null, []);

            $lines = array_merge($lines, is_array($drawing_lines) ? $drawing_lines : []);
            $areas = array_merge($areas, is_array($drawing_areas) ? $drawing_areas : []);
            $perimeters = array_merge($perimeters, is_array($drawing_perimeters) ? $drawing_perimeters : []);
        }

        $user = auth()->user();
        $userId = (int) $user->id;

        // 1) juntamos combinaciones únicas de trade_name + idunique
        $combinaciones_takeoff = [];

        foreach ($lines as $lineItem) {
            $trade_name = $this->extractTradeNameFromTakeoff($lineItem);
            $idunique = $this->extractIdUniqueFromTakeoff($lineItem);

            if ($trade_name !== '' && $idunique !== '') {
                $clave = mb_strtolower(trim($trade_name)) . '||' . trim($idunique);
                $combinaciones_takeoff[$clave] = [
                    'trade_name' => $trade_name,
                    'idunique' => $idunique,
                ];
            }
        }

        foreach ($perimeters as $perimeterItem) {
            $trade_name = $this->extractTradeNameFromTakeoff($perimeterItem);
            $idunique = $this->extractIdUniqueFromTakeoff($perimeterItem);

            if ($trade_name !== '' && $idunique !== '') {
                $clave = mb_strtolower(trim($trade_name)) . '||' . trim($idunique);
                $combinaciones_takeoff[$clave] = [
                    'trade_name' => $trade_name,
                    'idunique' => $idunique,
                ];
            }
        }

        foreach ($areas as $areaItem) {
            $trade_name = $this->extractTradeNameFromTakeoff($areaItem);
            $idunique = $this->extractIdUniqueFromTakeoff($areaItem);

            if ($trade_name !== '' && $idunique !== '') {
                $clave = mb_strtolower(trim($trade_name)) . '||' . trim($idunique);
                $combinaciones_takeoff[$clave] = [
                    'trade_name' => $trade_name,
                    'idunique' => $idunique,
                ];
            }
        }

        // 2) pool global: acumular materialesAgrupados de TODOS los takeoffs usados en el drawing
        $pool_materiales = [];

        foreach ($combinaciones_takeoff as $takeoff_data) {
            $materiales = $this->obtenerMaterialesDeWall(
                $userId,
                $takeoff_data['trade_name'],
                $projectId,
                $takeoff_data['idunique']
            );

            if (is_array($materiales) && count($materiales)) {
                foreach ($materiales as $item) {
                    $pool_materiales[] = $item;
                }
            }
        }
        // 3) con el pool global generas reportes por material
        $reports = $this->generarReportesPorMaterialDesdePool($pool_materiales);

        return response()->json([
            'ok' => true,
            'message' => 'Reports generated',
            'path' => $path,
            'trades_used' => array_values(array_unique(array_map(function ($item) {
                return $item['trade_name'];
            }, $combinaciones_takeoff))),
            'takeoffs_used' => array_values($combinaciones_takeoff),
            'reports' => $reports,
        ]);
    }


    // =========================================================
    // Helpers
    // =========================================================

    private function safeJson($value, $default)
    {
        if ($value === null || $value === '')
            return $default;

        // ya viene como array/obj
        if (is_array($value) || is_object($value))
            return $value;

        $decoded = json_decode((string) $value, true);
        if (json_last_error() !== JSON_ERROR_NONE)
            return $default;

        return $decoded;
    }

    private function obtenerPathProyecto($path)
    {
        $partes = array_values(array_filter(explode('/', $path)));
        // ["projects", "project58", "full", "archivo.png"]

        if (count($partes) >= 2) {
            return '/' . $partes[0] . '/' . $partes[1] . '/';
        }

        return '/';
    }

    private function extractTradeNameFromTakeoff($takeoffItem): string
    {
        if (!is_array($takeoffItem))
            return '';

        $meta = (isset($takeoffItem['meta']) && is_array($takeoffItem['meta'])) ? $takeoffItem['meta'] : [];

        // 1) Primero lo correcto (trade)
        $trade =
            trim((string) ($meta['trade_name'] ?? '')) ?:
            trim((string) ($meta['tradeName'] ?? '')) ?:
            trim((string) ($takeoffItem['trade_name'] ?? '')) ?:
            trim((string) ($takeoffItem['tradeName'] ?? ''));

        if ($trade !== '')
            return $trade;

        // 2) Fallback: algunos te lo guardan en name (pero NO siempre)
        $fallback =
            trim((string) ($meta['name'] ?? '')) ?:
            trim((string) ($takeoffItem['name'] ?? ''));

        return $fallback;
    }

    private function extractIdUniqueFromTakeoff($takeoffItem): string
    {
        if (!is_array($takeoffItem))
            return '';

        $meta = (isset($takeoffItem['meta']) && is_array($takeoffItem['meta'])) ? $takeoffItem['meta'] : [];

        $idunique =
            trim((string) ($meta['idunique'] ?? '')) ?:
            trim((string) ($meta['id_unique'] ?? '')) ?:
            trim((string) ($meta['idUnique'] ?? '')) ?:
            trim((string) ($takeoffItem['idunique'] ?? '')) ?:
            trim((string) ($takeoffItem['id_unique'] ?? '')) ?:
            trim((string) ($takeoffItem['idUnique'] ?? ''));

        return $idunique;
    }

    private function obtenerMaterialesDeWall(int $userId, string $tradeName, $projectId, string $idunique): array
    {
        $tradeName = trim((string) $tradeName);
        $idunique = trim((string) $idunique);

        if ($tradeName === '' || $idunique === '') {
            return [];
        }

        $existing_wall = Wall::where('name', $tradeName)
            ->where('idunique', $idunique)
            ->where('project_id', $projectId)
            ->orderByDesc('id')
            ->first();

        if (!isset($existing_wall->id)) {
            return [];
        }

        /*
         * bulk() recalcula directamente el wall guardado y, a diferencia de
         * CalculateData(), no recibe linked_openings en el request. Se agregan
         * únicamente a una copia temporal para que WallController aplique la
         * resta al material principal y genere las filas positivas del opening
         * antes de consolidar el reporte global.
         */
        $wallParaBulk = clone $existing_wall;
        $formDataBulk = $this->safeJson($wallParaBulk->formData ?? null, []);

        if (is_object($formDataBulk)) {
            $formDataBulk = (array) $formDataBulk;
        }

        if (!is_array($formDataBulk)) {
            $formDataBulk = [];
        }

        $formDataBulk['linked_openings'] = $this->obtenerOpeningsVinculados(
            (int) $existing_wall->project_id,
            $userId,
            $idunique
        );

        $wallParaBulk->formData = json_encode(
            $formDataBulk,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $wc = new WallController();

        try {
            $resultado = $wc->recalculate(null, $wallParaBulk);

            $materialesAgrupados = null;

            if (isset($resultado->materialesAgrupados)) {
                $materialesAgrupados = $resultado->materialesAgrupados;
            }
            if ($materialesAgrupados === null && isset($resultado->materiales_agrupados)) {
                $materialesAgrupados = $resultado->materiales_agrupados;
            }
            if ($materialesAgrupados === null && isset($resultado->materialsGrouped)) {
                $materialesAgrupados = $resultado->materialsGrouped;
            }

            $materialesAgrupados = $this->safeJson($materialesAgrupados, []);

            if (!is_array($materialesAgrupados)) {
                return [];
            }

            return $materialesAgrupados;

        } catch (\Throwable $th) {
            return [];
        }
    }

    private function generarReportesPorMaterialDesdePool(array $pool_materiales): array
    {
        $gruposPorMaterial = [];

        foreach ($pool_materiales as $item) {
            if (!is_array($item)) {
                continue;
            }

            $canon = $this->buildMaterialCanonicalData($item);
            $grupoKey = $canon['group_key'];

            if (!isset($gruposPorMaterial[$grupoKey])) {
                $gruposPorMaterial[$grupoKey] = [
                    'group_key' => $grupoKey,

                    'trade_name' => $canon['trade_display'],
                    'trade_sort_key' => $canon['trade_key'],

                    'material_class_name' => $canon['class_display'],
                    'material_class_sort_key' => $canon['class_key'],

                    'material' => $canon['material'],
                    'material_name' => $canon['material_display'],
                    'material_sort_key' => $canon['material_key'],

                    'items' => [],
                ];
            } else {
                // Si el grupo ya existe, conserva el display más completo
                if (
                    mb_strlen($canon['trade_display']) > mb_strlen($gruposPorMaterial[$grupoKey]['trade_name'])
                ) {
                    $gruposPorMaterial[$grupoKey]['trade_name'] = $canon['trade_display'];
                }

                if (
                    mb_strlen($canon['class_display']) > mb_strlen($gruposPorMaterial[$grupoKey]['material_class_name'])
                ) {
                    $gruposPorMaterial[$grupoKey]['material_class_name'] = $canon['class_display'];
                }

                if (
                    mb_strlen($canon['material_display']) > mb_strlen($gruposPorMaterial[$grupoKey]['material_name'])
                ) {
                    $gruposPorMaterial[$grupoKey]['material_name'] = $canon['material_display'];
                }

                if (
                    empty($gruposPorMaterial[$grupoKey]['material']) &&
                    !empty($canon['material'])
                ) {
                    $gruposPorMaterial[$grupoKey]['material'] = $canon['material'];
                }
            }

            $gruposPorMaterial[$grupoKey]['items'][] = $item;
        }

        // Ordenar items dentro de cada material por takeoff alfabéticamente
        foreach ($gruposPorMaterial as &$grupo) {
            usort($grupo['items'], function ($itemIzquierdo, $itemDerecho) {
                $takeoffIzquierdo = $this->buildTakeoffCanonicalName(is_array($itemIzquierdo) ? $itemIzquierdo : []);
                $takeoffDerecho = $this->buildTakeoffCanonicalName(is_array($itemDerecho) ? $itemDerecho : []);

                return $this->compareNaturalAlphabetic($takeoffIzquierdo, $takeoffDerecho);
            });
        }
        unset($grupo);

        // Ordenar grupos finales alfabéticamente:
        // 1. trade
        // 2. class
        // 3. material
        uasort($gruposPorMaterial, function ($grupoIzquierdo, $grupoDerecho) {
            $comparacionTrade = $this->compareNaturalAlphabetic(
                (string) ($grupoIzquierdo['trade_name'] ?? ''),
                (string) ($grupoDerecho['trade_name'] ?? '')
            );

            if ($comparacionTrade !== 0) {
                return $comparacionTrade;
            }

            $comparacionClase = $this->compareNaturalAlphabetic(
                (string) ($grupoIzquierdo['material_class_name'] ?? ''),
                (string) ($grupoDerecho['material_class_name'] ?? '')
            );

            if ($comparacionClase !== 0) {
                return $comparacionClase;
            }

            return $this->compareNaturalAlphabetic(
                (string) ($grupoIzquierdo['material_name'] ?? ''),
                (string) ($grupoDerecho['material_name'] ?? '')
            );
        });

        $reports = [];

        foreach ($gruposPorMaterial as $grupo) {
            $takeoffs = [];

            foreach ($grupo['items'] as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $takeoffName = $this->buildTakeoffCanonicalName($item);
                $takeoffKey = $this->normalizeTextKey($takeoffName);

                if (!isset($takeoffs[$takeoffKey])) {
                    $takeoffs[$takeoffKey] = [
                        'takeoff_name' => $takeoffName,
                        'quantity' => 0,
                        'waste' => 0,
                        'sq_ft' => 0,
                        'units' => 0,
                        'units_total' => 0,
                        'measuring' => 0,
                        'cost' => 0,
                        'tax' => 0,
                        'cost1' => 0,
                        'cost_day' => 0,
                        'burden' => 0,
                        'lab_cost' => 0,
                        'days' => 0,
                        'cost2' => 0,
                        'sub_total' => 0,
                        'oh' => 0,
                        'profit' => 0,
                        'weather' => 0,
                        'total' => 0,
                        'op_unit' => $item['op_unit'] ?? '',
                    ];
                }

                $takeoffs[$takeoffKey]['quantity'] += (float) ($item['quantity'] ?? 0);
                $takeoffs[$takeoffKey]['waste'] += (float) ($item['waste'] ?? 0);
                $takeoffs[$takeoffKey]['sq_ft'] += (float) ($item['sq_ft'] ?? 0);
                $takeoffs[$takeoffKey]['units'] += (float) ($item['units'] ?? 0);
                $takeoffs[$takeoffKey]['units_total'] += (float) ($item['units_total'] ?? 0);
                $takeoffs[$takeoffKey]['measuring'] += (float) ($item['measuring'] ?? 0);

                $takeoffs[$takeoffKey]['cost'] += (float) ($item['cost'] ?? 0);
                $takeoffs[$takeoffKey]['tax'] += (float) ($item['tax'] ?? 0);
                $takeoffs[$takeoffKey]['cost1'] += (float) ($item['cost1'] ?? ((float) ($item['cost'] ?? 0) + (float) ($item['tax'] ?? 0)));
                $takeoffs[$takeoffKey]['cost_day'] += (float) ($item['cost_day'] ?? 0);
                $takeoffs[$takeoffKey]['burden'] += (float) ($item['burden'] ?? 0);
                $takeoffs[$takeoffKey]['lab_cost'] += (float) ($item['lab_cost'] ?? 0);
                $takeoffs[$takeoffKey]['days'] += (float) ($item['days'] ?? 0);
                $takeoffs[$takeoffKey]['cost2'] += (float) ($item['cost2'] ?? 0);
                $takeoffs[$takeoffKey]['sub_total'] += (float) ($item['sub_total'] ?? 0);
                $takeoffs[$takeoffKey]['oh'] += (float) ($item['oh'] ?? 0);
                $takeoffs[$takeoffKey]['profit'] += (float) ($item['profit'] ?? 0);
                $takeoffs[$takeoffKey]['weather'] += (float) ($item['weather'] ?? 0);

                $valorTotal = isset($item['total']) ? (float) $item['total'] : 0;
                if ($valorTotal == 0) {
                    $valorTotal =
                        (float) ($item['sub_total'] ?? 0) +
                        (float) ($item['oh'] ?? 0) +
                        (float) ($item['profit'] ?? 0) +
                        (float) ($item['weather'] ?? 0);
                }

                $takeoffs[$takeoffKey]['total'] += $valorTotal;

                if (($takeoffs[$takeoffKey]['op_unit'] ?? '') === '' && !empty($item['op_unit'])) {
                    $takeoffs[$takeoffKey]['op_unit'] = $item['op_unit'];
                }
            }

            $rows = array_values($takeoffs);

            usort($rows, function ($filaIzquierda, $filaDerecha) {
                return $this->compareNaturalAlphabetic(
                    (string) ($filaIzquierda['takeoff_name'] ?? ''),
                    (string) ($filaDerecha['takeoff_name'] ?? '')
                );
            });

            $grupo['rows'] = $rows;

            $html = $this->generarTablaHtmlPorMaterial($grupo);

            if (trim($html) !== '') {
                $reports[] = [
                    'title' => (string) $grupo['trade_name'] . ' - ' . (string) $grupo['material_name'],
                    'trade_name' => (string) $grupo['trade_name'],
                    'material_class_name' => (string) $grupo['material_class_name'],
                    'material_name' => (string) $grupo['material_name'],
                    'html' => $html,
                ];
            }
        }

        // Blindaje extra: ordenar el array final también
        usort($reports, function ($reporteIzquierdo, $reporteDerecho) {
            $comparacionTrade = $this->compareNaturalAlphabetic(
                (string) ($reporteIzquierdo['trade_name'] ?? ''),
                (string) ($reporteDerecho['trade_name'] ?? '')
            );

            if ($comparacionTrade !== 0) {
                return $comparacionTrade;
            }

            $comparacionClase = $this->compareNaturalAlphabetic(
                (string) ($reporteIzquierdo['material_class_name'] ?? ''),
                (string) ($reporteDerecho['material_class_name'] ?? '')
            );

            if ($comparacionClase !== 0) {
                return $comparacionClase;
            }

            return $this->compareNaturalAlphabetic(
                (string) ($reporteIzquierdo['material_name'] ?? ''),
                (string) ($reporteDerecho['material_name'] ?? '')
            );
        });

        return $reports;
    }

    private function generarTablaHtmlPorMaterial(array $grupo): string
    {
        $num = function ($v) {
            return is_numeric($v) ? (float) $v : 0.0;
        };

        $money = function ($v) use ($num) {
            return '$ ' . number_format($num($v), 2);
        };

        $trade = htmlspecialchars((string) ($grupo['trade_name'] ?? 'TRADE'), ENT_QUOTES, 'UTF-8');

        $material = isset($grupo['material']) && is_array($grupo['material']) ? $grupo['material'] : [];
        $materialNombre = htmlspecialchars((string) ($grupo['material_name'] ?? ($material['name'] ?? 'Material')), ENT_QUOTES, 'UTF-8');
        $materialClassLabel = htmlspecialchars((string) ($grupo['material_class_name'] ?? 'CLASS'), ENT_QUOTES, 'UTF-8');

        // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ AQUÃƒÆ’Ã‚Â: usar items (no rows)
        $rows = isset($grupo['rows']) && is_array($grupo['rows']) ? $grupo['rows'] : [];
        if (!count($rows)) {
            return '';
        }

        // mismas columnas numÃƒÆ’Ã‚Â©ricas que generarTablaHtml()
        $sumCols = [
            'measuring',
            'units',
            'waste',
            'sq_ft',
            'units_total',
            'cost_ea',
            'cost',
            'tax',
            'cost1',
            'cost_day',
            'burden',
            'lab_cost',
            'days',
            'cost2',
            'sub_total',
            'oh',
            'profit',
            'weather',
            'total'
        ];
        $tot = array_fill_keys($sumCols, 0.0);

        $html = '
        <style>
            .xeon-report-card{border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin:14px 0;font-family:Arial, sans-serif;}
            .xeon-report-head{padding:10px 12px;background:#f8fafc;border-bottom:1px solid #e5e7eb;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
            .xeon-pill{display:inline-block;padding:4px 10px;border:1px solid #cbd5e1;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;background:#fff}
            .xeon-title{font-weight:800}

            .xeon-table{width:100%;border-collapse:collapse}
            .xeon-table th,.xeon-table td{border-bottom:1px solid #eef2f7;padding:8px 10px;font-size:12px;white-space:nowrap}
            .xeon-table th{text-transform:uppercase;font-size:11px;letter-spacing:.3px;background:#fff}
            .xeon-right{text-align:right}
            .xeon-total-row td{font-weight:800;background:#fffdf2;border-top:2px solid #111827}

            .col-id{width:60px;text-align:center;font-weight:800}
            .col-takeoff{min-width:180px;}
        </style>

        <div class="xeon-report-card">
            <div class="xeon-report-head">
                <span class="xeon-pill">TRADE | ' . $trade . '</span>
                <span class="xeon-pill">MATERIAL | ' . $materialClassLabel . '</span>
                <span class="xeon-title">' . $materialNombre . '</span>
            </div>

            <div style="width:100%;overflow:auto;">
                <table class="xeon-table">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-takeoff">Takeoff</th>
                            <th class="xeon-right">Measuring Area</th>
                            <th class="xeon-right">Units</th>
                            <th class="xeon-right">Waste</th>
                            <th class="xeon-right">SQ FT</th>
                            <th class="xeon-right">Units Total</th>
                            <th class="xeon-right">Cost ea</th>
                            <th class="xeon-right">Cost</th>
                            <th class="xeon-right">Tax</th>
                            <th class="xeon-right">Cost + Tax</th>
                            <th class="xeon-right">Cost/day</th>
                            <th class="xeon-right">Burden</th>
                            <th class="xeon-right">Lab Cost</th>
                            <th class="xeon-right">Days</th>
                            <th class="xeon-right">Cost (2)</th>
                            <th class="xeon-right">Sub Total</th>
                            <th class="xeon-right">OH</th>
                            <th class="xeon-right">Profit</th>
                            <th class="xeon-right">Weather</th>
                            <th class="xeon-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        $i = 1;
        foreach ($rows as $item) {
            if (!is_array($item))
                continue;

            $takeoffName = htmlspecialchars((string) ($item['takeoff_name'] ?? 'takeoff'), ENT_QUOTES, 'UTF-8');

            $measuring = $num($item['measuring'] ?? 0);
            $units = $num($item['units'] ?? 0);
            //$quantity  = $num($item['quantity'] ?? 0);
            $waste = $num($item['waste'] ?? 0);
            $sqFt = $num($item['sq_ft'] ?? 0);
            $units_total = $num($item['units_total'] ?? 0);

            $costEa = $num($item['cost_ea'] ?? 0);
            $cost = $num($item['cost'] ?? 0);
            $tax = $num($item['tax'] ?? 0);

            $cost1 = $num($item['cost1'] ?? ($cost + $tax));
            $costDay = $num($item['cost_day'] ?? 0);
            $burden = $num($item['burden'] ?? 0);
            $labCost = $num($item['lab_cost'] ?? 0);
            $days = $num($item['days'] ?? 0);
            $cost2 = $num($item['cost2'] ?? 0);

            $subTotal = $num($item['sub_total'] ?? 0);
            $oh = $num($item['oh'] ?? 0);
            $profit = $num($item['profit'] ?? 0);
            $weather = $num($item['weather'] ?? 0);
            $total = $num($item['total'] ?? ($subTotal + $oh + $profit + $weather));

            // acumular
            $tot['measuring'] += $measuring;
            $tot['op_unit'] = $item['op_unit'] != '' ? $item['op_unit'] : 'lf';
            $tot['units'] += $units;
            $tot['waste'] += $waste;
            $tot['sq_ft'] += $sqFt;
            $tot['units_total'] += $units_total;

            $tot['cost_ea'] += $costEa;
            $tot['cost'] += $cost;
            $tot['tax'] += $tax;
            $tot['cost1'] += $cost1;

            $tot['cost_day'] += $costDay;
            $tot['burden'] += $burden;
            $tot['lab_cost'] += $labCost;
            $tot['days'] += $days;

            $tot['cost2'] += $cost2;
            $tot['sub_total'] += $subTotal;
            $tot['oh'] += $oh;
            $tot['profit'] += $profit;
            $tot['weather'] += $weather;
            $tot['total'] += $total;

            $html .= '
                <tr>
                    <td class="col-id">' . $i . '</td>
                    <td class="col-takeoff">' . $takeoffName . '</td>
                    <td class="xeon-right">' . number_format($measuring, 2) . ' ' . htmlspecialchars((string) $item['op_unit'] != '' ? $item['op_unit'] : 'lf', ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="xeon-right">' . number_format($units, 2) . ' ' . htmlspecialchars((string) $material['default_unit'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="xeon-right">' . number_format($waste, 2) . '</td>
                    <td class="xeon-right">' . number_format($sqFt, 2) . ' ' . htmlspecialchars((string) $item['op_unit'] != '' ? $item['op_unit'] : 'lf', ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="xeon-right">' . number_format($units_total, 2) . ' ' . htmlspecialchars((string) $material['default_unit'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td class="xeon-right">' . $money($costEa) . '</td>
                    <td class="xeon-right">' . $money($cost) . '</td>
                    <td class="xeon-right">' . $money($tax) . '</td>
                    <td class="xeon-right">' . $money($cost1) . '</td>
                    <td class="xeon-right">' . $money($costDay) . '</td>
                    <td class="xeon-right">' . $money($burden) . '</td>
                    <td class="xeon-right">' . $money($labCost) . '</td>
                    <td class="xeon-right">' . number_format($days, 2) . '</td>
                    <td class="xeon-right">' . $money($cost2) . '</td>
                    <td class="xeon-right">' . $money($subTotal) . '</td>
                    <td class="xeon-right">' . $money($oh) . '</td>
                    <td class="xeon-right">' . $money($profit) . '</td>
                    <td class="xeon-right">' . $money($weather) . '</td>
                    <td class="xeon-right">' . $money($total) . '</td>
                </tr>
            ';
            $i++;
        }

        $html .= '
                    </tbody>
                    <tfoot>
                        <tr class="xeon-total-row">
                            <td colspan="2">Total</td>
                            <td class="xeon-right">' . number_format($tot['measuring'], 2) . ' ' . htmlspecialchars((string) $tot['op_unit'], ENT_QUOTES, 'UTF-8') . '</td>
                            <td class="xeon-right">' . number_format($tot['units'], 2) . ' ' . htmlspecialchars((string) $material['default_unit'], ENT_QUOTES, 'UTF-8') . '</td>
                            <td class="xeon-right">' . number_format($tot['waste'], 2) . '</td>
                            <td class="xeon-right">' . number_format($tot['sq_ft'], 2) . ' ' . htmlspecialchars((string) $tot['op_unit'], ENT_QUOTES, 'UTF-8') . '</td>
                            <td class="xeon-right">' . number_format($tot['units_total'], 2) . ' ' . htmlspecialchars((string) $material['default_unit'], ENT_QUOTES, 'UTF-8') . '</td>
                            <td class="xeon-right">' . $money($tot['cost_ea']) . '</td>
                            <td class="xeon-right">' . $money($tot['cost']) . '</td>
                            <td class="xeon-right">' . $money($tot['tax']) . '</td>
                            <td class="xeon-right">' . $money($tot['cost1']) . '</td>
                            <td class="xeon-right">' . $money($tot['cost_day']) . '</td>
                            <td class="xeon-right">' . $money($tot['burden']) . '</td>
                            <td class="xeon-right">' . $money($tot['lab_cost']) . '</td>
                            <td class="xeon-right">' . number_format($tot['days'], 2) . '</td>
                            <td class="xeon-right">' . $money($tot['cost2']) . '</td>
                            <td class="xeon-right">' . $money($tot['sub_total']) . '</td>
                            <td class="xeon-right">' . $money($tot['oh']) . '</td>
                            <td class="xeon-right">' . $money($tot['profit']) . '</td>
                            <td class="xeon-right">' . $money($tot['weather']) . '</td>
                            <td class="xeon-right">' . $money($tot['total']) . '</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        ';

        return $html;
    }

    public function bulkExcel(Request $request)
    {
        $path = trim((string) $request->input('path', ''));
        $projectId = trim((string) $request->input('projectId', ''));

        $selectedColumns = $request->input('selected_columns', []);

        if (!is_array($selectedColumns)) {
            $selectedColumns = [];
        }

        $selectedColumns = array_map('intval', $selectedColumns);

        $selectedColumns = array_values(array_filter($selectedColumns, function ($columnIndex) {
            return $columnIndex > 0;
        }));

        if ($path === '') {
            return response()->json(['ok' => false, 'message' => 'Missing path'], 422);
        }

        // Reutilizamos bulk()
        $bulkResponse = $this->bulk($request)->getData(true);

        if (empty($bulkResponse['ok']) || empty($bulkResponse['reports'])) {
            return response()->json(['ok' => false, 'message' => 'No reports to export'], 404);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet Reports');

        // Header general
        $sheet->setCellValue('A1', 'SHEET REPORTS');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $currentRow = 3;
        $htmlReader = new HtmlReader();

        foreach ($bulkResponse['reports'] as $report) {

            $title = trim((string) ($report['title'] ?? 'Report'));
            $html = (string) ($report['html'] ?? '');

            if ($html === '') {
                continue;
            }

            if (count($selectedColumns) > 0) {
                $html = $this->filterHtmlTableColumns($html, $selectedColumns);
            }

            // ÃƒÂ°Ã…Â¸Ã…Â¸Ã‚Â¦ TÃƒÆ’Ã‚Â­tulo del bloque (tipo card header)
            $sheet->setCellValue("A{$currentRow}", $title);
            $sheet->mergeCells("A{$currentRow}:" . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(25) . "{$currentRow}");
            $sheet->getStyle("A{$currentRow}:K{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF1F5F9'],
                ],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;

            // HTML completo
            $fullHtml = '<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';

            $tmpFile = tempnam(sys_get_temp_dir(), 'bulkrep_') . '.html';
            file_put_contents($tmpFile, $fullHtml);

            $tmpBook = $htmlReader->load($tmpFile);
            @unlink($tmpFile);

            $tmpSheet = $tmpBook->getActiveSheet();

            // Detectar filas reales (sin NBSP)
            $firstDataRow = null;
            $lastDataRow = null;

            $tmpMaxRow = (int) $tmpSheet->getHighestRow();
            $tmpMaxCol = Coordinate::columnIndexFromString($tmpSheet->getHighestColumn());

            for ($r = 1; $r <= $tmpMaxRow; $r++) {
                if ($this->rowHasRealContent($tmpSheet, $r, $tmpMaxCol)) {
                    if ($firstDataRow === null)
                        $firstDataRow = $r;
                    $lastDataRow = $r;
                }
            }

            if ($firstDataRow === null || $lastDataRow === null) {
                continue;
            }

            $startBlockRow = $currentRow;

            $endBlockRow = $this->appendSheetBlock(
                $tmpSheet,
                $sheet,
                $currentRow,
                1,
                $firstDataRow,
                $lastDataRow
            );

            $this->styleReportBlock($sheet, $startBlockRow, $endBlockRow);

            $currentRow = $endBlockRow + 2;
        }

        // Autosize
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'sheet-reports-' . date('Ymd_His') . '.xlsx';
        $dir = storage_path('app/tmp');
        if (!is_dir($dir))
            @mkdir($dir, 0777, true);

        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;

        (new Xlsx($spreadsheet))->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    private function filterHtmlTableColumns(string $html, array $selectedColumns): string
    {
        if (trim($html) === '') {
            return $html;
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();

        $dom->loadHTML(
            '<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $xpath = new \DOMXPath($dom);
        $rows = $xpath->query('//tr');

        foreach ($rows as $row) {
            $realColumnIndex = 1;
            $cells = [];

            foreach ($row->childNodes as $childNode) {
                if ($childNode->nodeName === 'td' || $childNode->nodeName === 'th') {
                    $cells[] = $childNode;
                }
            }

            foreach ($cells as $cell) {
                $originalColspan = (int) $cell->getAttribute('colspan');

                if ($originalColspan <= 0) {
                    $originalColspan = 1;
                }

                $coveredColumns = [];

                for ($i = 0; $i < $originalColspan; $i++) {
                    $coveredColumns[] = $realColumnIndex + $i;
                }

                $visibleCoveredColumns = array_values(array_filter($coveredColumns, function ($columnIndex) use ($selectedColumns) {
                    return in_array($columnIndex, $selectedColumns, true);
                }));

                if (count($visibleCoveredColumns) === 0) {
                    if ($cell->parentNode) {
                        $cell->parentNode->removeChild($cell);
                    }
                } else {
                    if (count($visibleCoveredColumns) === 1) {
                        $cell->removeAttribute('colspan');
                    }

                    if (count($visibleCoveredColumns) > 1) {
                        $cell->setAttribute('colspan', count($visibleCoveredColumns));
                    }
                }

                $realColumnIndex += $originalColspan;
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $filteredHtml = '';

        if ($body) {
            foreach ($body->childNodes as $childNode) {
                $filteredHtml .= $dom->saveHTML($childNode);
            }
        }

        libxml_clear_errors();

        return $filteredHtml;
    }

    public function bulkExcelFromHtml(Request $request)
    {
        $reports = $request->input('reports', []);

        if (!is_array($reports) || !count($reports)) {
            return response()->json(['ok' => false, 'message' => 'No reports provided'], 422);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet Reports');

        // Header general
        $sheet->setCellValue('A1', 'SHEET REPORTS');
        $sheet->mergeCells('A1:U1'); // U = 21 cols (tu tabla ya creciÃƒÆ’Ã‚Â³)
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $currentRow = 3;
        $htmlReader = new HtmlReader();

        foreach ($reports as $report) {
            if (!is_array($report))
                continue;

            $title = trim((string) ($report['title'] ?? 'Report'));
            $html = (string) ($report['html'] ?? '');

            if ($html === '')
                continue;

            // TÃƒÆ’Ã‚Â­tulo del bloque (tipo card header)
            $sheet->setCellValue("A{$currentRow}", $title);
            $sheet->mergeCells("A{$currentRow}:U{$currentRow}");
            $sheet->getStyle("A{$currentRow}:U{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;

            // HTML -> tmp sheet
            $fullHtml = '<html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';
            $tmpFile = tempnam(sys_get_temp_dir(), 'bulkrep_') . '.html';
            file_put_contents($tmpFile, $fullHtml);

            $tmpBook = $htmlReader->load($tmpFile);
            @unlink($tmpFile);

            $tmpSheet = $tmpBook->getActiveSheet();

            // Detectar filas reales (sin NBSP)
            $firstDataRow = null;
            $lastDataRow = null;

            $tmpMaxRow = (int) $tmpSheet->getHighestRow();
            $tmpMaxCol = Coordinate::columnIndexFromString($tmpSheet->getHighestColumn());

            for ($r = 1; $r <= $tmpMaxRow; $r++) {
                if ($this->rowHasRealContent($tmpSheet, $r, $tmpMaxCol)) {
                    if ($firstDataRow === null)
                        $firstDataRow = $r;
                    $lastDataRow = $r;
                }
            }

            if ($firstDataRow === null || $lastDataRow === null) {
                continue;
            }

            $startBlockRow = $currentRow;

            // Copiar bloque colapsando filas vacÃƒÆ’Ã‚Â­as
            $endBlockRow = $this->appendSheetBlock(
                $tmpSheet,
                $sheet,
                $currentRow,
                1,
                $firstDataRow,
                $lastDataRow
            );

            // Estilo a TODO el ancho real
            $this->styleReportBlockDynamic($sheet, $startBlockRow, $endBlockRow);

            $currentRow = $endBlockRow + 2;
        }

        // Auto-size columnas (hasta U)
        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'sheet-reports-' . date('Ymd_His') . '.xlsx';
        $dir = storage_path('app/tmp');
        if (!is_dir($dir))
            @mkdir($dir, 0777, true);

        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
        (new Xlsx($spreadsheet))->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Copia un bloque completo de una hoja a otra (valores + estilos).
     * $startRowDestino: fila inicial en hoja destino
     * $startColDestino: columna inicial (1 = A)
     */
    private function appendSheetBlock(
        $srcSheet,
        $dstSheet,
        int $startRowDestino,
        int $startColDestino = 1,
        int $srcStartRow = 1,
        ?int $srcEndRow = null
    ): int {
        $srcEndRow = $srcEndRow ?? (int) $srcSheet->getHighestRow();
        $srcMaxCol = Coordinate::columnIndexFromString($srcSheet->getHighestColumn());

        $dstRow = $startRowDestino;

        for ($r = $srcStartRow; $r <= $srcEndRow; $r++) {

            if (!$this->rowHasRealContent($srcSheet, $r, $srcMaxCol)) {
                continue;
            }

            for ($c = 1; $c <= $srcMaxCol; $c++) {
                $srcAddr = Coordinate::stringFromColumnIndex($c) . $r;
                $dstAddr = Coordinate::stringFromColumnIndex($startColDestino + ($c - 1)) . $dstRow;

                $dstSheet->setCellValue($dstAddr, $srcSheet->getCell($srcAddr)->getValue());
                $dstSheet->duplicateStyle($srcSheet->getStyle($srcAddr), $dstAddr);
            }

            $dstRow++;
        }

        return $dstRow - 1;
    }

    private function styleReportBlock(Worksheet $sheet, int $startRow, int $endRow): void
    {
        // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ Detecta la ÃƒÆ’Ã‚Âºltima columna real en ese bloque (escaneando filas)
        $maxColIndex = 1;
        for ($r = $startRow; $r <= $endRow; $r++) {
            $highestCol = $sheet->getHighestColumn($r); // ej. "U"
            $colIndex = Coordinate::columnIndexFromString($highestCol);
            if ($colIndex > $maxColIndex)
                $maxColIndex = $colIndex;
        }

        $startCol = 'A';
        $endCol = Coordinate::stringFromColumnIndex($maxColIndex);

        // 1) Bordes + wrap para TODO el bloque
        $sheet->getStyle("{$startCol}{$startRow}:{$endCol}{$endRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE5E7EB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // 2) Detectar HEADER: ahora buscamos la fila que contenga "TAKEOFF" en la columna B
        // (porque columna A es "ID", columna B es "Takeoff")
        $headerRow = null;
        for ($r = $startRow; $r <= min($endRow, $startRow + 15); $r++) {
            $b = strtoupper(trim((string) $sheet->getCell("B{$r}")->getValue()));
            $a = strtoupper(trim((string) $sheet->getCell("A{$r}")->getValue()));
            if ($b === 'TAKEOFF' || $a === 'ID') {
                $headerRow = $r;
                break;
            }
        }

        // 3) Detectar TOTAL: sigue en la primera columna del tfoot ("Total")
        $totalRow = null;
        for ($r = $endRow; $r >= max($startRow, $endRow - 20); $r--) {
            $val = strtoupper(trim((string) $sheet->getCell("A{$r}")->getValue()));
            if ($val === 'TOTAL') {
                $totalRow = $r;
                break;
            }
        }

        // 4) Header style
        if ($headerRow !== null) {
            $sheet->getStyle("{$startCol}{$headerRow}:{$endCol}{$headerRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF111827']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(18);

            // 5) Zebra rows (solo datos)
            $dataStart = $headerRow + 1;
            $dataEnd = ($totalRow !== null) ? $totalRow - 1 : $endRow;

            for ($r = $dataStart; $r <= $dataEnd; $r++) {
                if ((($r - $dataStart) % 2) === 0) {
                    $sheet->getStyle("{$startCol}{$r}:{$endCol}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFAFAFA']],
                    ]);
                }
            }
        }

        // 6) Total style
        if ($totalRow !== null) {
            $sheet->getStyle("{$startCol}{$totalRow}:{$endCol}{$totalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF111827']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF7ED']],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF111827']],
                ],
            ]);
        }

        // 7) AlineaciÃƒÆ’Ã‚Â³n: A y B a la izquierda, el resto derecha
        $sheet->getStyle("A{$startRow}:B{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C{$startRow}:{$endCol}{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 8) Formatos: ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œnÃƒÆ’Ã‚ÂºmerosÃƒÂ¢Ã¢â€šÂ¬Ã‚Â y ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œdineroÃƒÂ¢Ã¢â€šÂ¬Ã‚Â
        // Tu tabla: A=ID, B=Takeoff, C..G nÃƒÆ’Ã‚Âºmeros, H.. fin dinero (mÃƒÆ’Ã‚Â¡s o menos)
        // Lo hacemos robusto: C:G nÃƒÆ’Ã‚Âºmeros (si existen), H:last dinero
        if ($maxColIndex >= 3) {
            $sheet->getStyle("C{$startRow}:" . Coordinate::stringFromColumnIndex(min(7, $maxColIndex)) . "{$endRow}")
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }
        if ($maxColIndex >= 8) {
            $sheet->getStyle("H{$startRow}:{$endCol}{$endRow}")
                ->getNumberFormat()->setFormatCode('"$"#,##0.00');
        }
    }

    private function normalizeCellValue($v): string
    {
        $s = (string) ($v ?? '');
        $s = str_replace(["\xC2\xA0", "\xA0"], ' ', $s);
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }

    private function rowHasRealContent($sheet, int $row, int $maxCol): bool
    {
        for ($c = 1; $c <= $maxCol; $c++) {
            $addr = Coordinate::stringFromColumnIndex($c) . $row;
            if ($this->normalizeCellValue($sheet->getCell($addr)->getValue()) !== '') {
                return true;
            }
        }
        return false;
    }

    private function normalizeTextKey($value): string
    {
        $texto = is_scalar($value) ? (string) $value : '';

        $texto = trim($texto);
        $texto = preg_replace('/\s+/u', ' ', $texto);
        $texto = mb_strtoupper($texto, 'UTF-8');

        // Quitar acentos
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($texto === false) {
            $texto = '';
        }

        $texto = preg_replace('/[^A-Z0-9]+/', ' ', $texto);
        $texto = preg_replace('/\s+/u', ' ', $texto);

        return trim($texto);
    }

    private function cleanDisplayText($value, string $fallback = ''): string
    {
        $texto = is_scalar($value) ? (string) $value : '';
        $texto = trim($texto);
        $texto = preg_replace('/\s+/u', ' ', $texto);

        return $texto !== '' ? $texto : $fallback;
    }

    private function firstNotEmptyText(array $sources, string $fallback = ''): string
    {
        foreach ($sources as $value) {
            if (is_string($value) || is_numeric($value)) {
                $texto = trim((string) $value);
                if ($texto !== '') {
                    return $texto;
                }
            }
        }

        return $fallback;
    }

    private function firstNotEmptyNumber(array $sources, $fallback = 0)
    {
        foreach ($sources as $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return $value + 0;
            }
        }

        return $fallback;
    }

    private function buildMaterialCanonicalData(array $item): array
    {
        $material = isset($item['material']) && is_array($item['material']) ? $item['material'] : [];

        $tradeDisplay = $this->cleanDisplayText(
            $this->firstNotEmptyText([
                $item['trade_name'] ?? null,
                $item['tradeName'] ?? null,
                $item['trade'] ?? null,
                $item['division_name'] ?? null,
            ], 'TRADE'),
            'TRADE'
        );

        $classDisplay = $this->cleanDisplayText(
            $this->firstNotEmptyText([
                $item['material_class_name'] ?? null,
                $item['class'] ?? null,
                $item['material_class'] ?? null,
                $material['material_class_name'] ?? null,
                $material['class'] ?? null,
            ], 'CLASS'),
            'CLASS'
        );

        $materialDisplay = $this->cleanDisplayText(
            $this->firstNotEmptyText([
                $material['name'] ?? null,
                $item['material_name'] ?? null,
                $item['name'] ?? null,
                $item['description'] ?? null,
            ], 'Material'),
            'Material'
        );

        $materialId = $this->cleanDisplayText(
            $this->firstNotEmptyText([
                $material['id'] ?? null,
                $item['material_id'] ?? null,
            ], '')
        );

        $materialUniqueId = $this->cleanDisplayText(
            $this->firstNotEmptyText([
                $material['unique_id'] ?? null,
                $item['material_unique_id'] ?? null,
                $item['unique_id'] ?? null,
            ], '')
        );

        $classId = $this->cleanDisplayText(
            $this->firstNotEmptyText([
                $material['material_class_id'] ?? null,
                $item['material_class_id'] ?? null,
            ], '')
        );

        $tradeKey = $this->normalizeTextKey($tradeDisplay);
        $classKey = $this->normalizeTextKey($classDisplay);
        $materialKey = $this->normalizeTextKey($materialDisplay);
        $descriptionKey = $this->normalizeTextKey($item['description'] ?? '');

        // Prioridad:
        // 1. unique_id
        // 2. id
        // 3. nombre normalizado
        $materialIdentityKey = '';
        if ($materialUniqueId !== '') {
            $materialIdentityKey = 'UNIQUE:' . $this->normalizeTextKey($materialUniqueId);
        }

        if ($materialIdentityKey === '' && $materialId !== '') {
            $materialIdentityKey = 'ID:' . $this->normalizeTextKey($materialId);
        }

        if ($materialIdentityKey === '') {
            $materialIdentityKey = 'NAME:' . $materialKey;
        }

        // Si la clase no viene, usa nombre de clase; si tampoco, CLASS
        $classIdentityKey = $classId !== ''
            ? 'CLASSID:' . $this->normalizeTextKey($classId)
            : 'CLASSNAME:' . $classKey;

        $groupKey = implode('|', [
            $tradeKey !== '' ? $tradeKey : 'TRADE',
            $classIdentityKey !== '' ? $classIdentityKey : 'CLASS',
            $materialIdentityKey !== '' ? $materialIdentityKey : 'MATERIAL',
        ]);

        return [
            'group_key' => $groupKey,

            'trade_display' => $tradeDisplay,
            'trade_key' => $tradeKey !== '' ? $tradeKey : 'TRADE',

            'class_display' => $classDisplay,
            'class_key' => $classKey !== '' ? $classKey : 'CLASS',

            'material_display' => $materialDisplay,
            'material_key' => $materialKey !== '' ? $materialKey : 'MATERIAL',

            'material_id' => $materialId,
            'material_unique_id' => $materialUniqueId,
            'description_key' => $descriptionKey,

            'material' => $material,
        ];
    }

    private function buildTakeoffCanonicalName(array $item): string
    {
        $takeoffName = $this->firstNotEmptyText([
            $item['takeoff_name'] ?? null,
            $item['takeoff'] ?? null,
            $item['name'] ?? null,
            $item['description'] ?? null,
        ], 'takeoff');

        return $this->cleanDisplayText($takeoffName, 'takeoff');
    }

    private function compareNaturalAlphabetic(string $textoA, string $textoB): int
    {
        return strnatcasecmp(
            $this->normalizeTextKey($textoA),
            $this->normalizeTextKey($textoB)
        );
    }

    public function deleteTemplate(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $userId = auth()->id();

        $deleted = LineTemplate::where('id', $request->id)
            ->where('user_id', $userId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => 404,
                'message' => 'Template not found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Template deleted successfully',
        ]);
    }

    public function uploadAnnotationPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
            'project_id' => 'required|integer',
        ]);

        try {
            $projectId = (int) $request->project_id;
            $file = $request->file('photo');

            $directory = "projects/project{$projectId}/photos";

            $extension = $file->getClientOriginalExtension();
            $fileName = 'annotation_photo_' . time() . '_' . Str::random(8) . '.' . $extension;

            StorageService::putFileAs($directory, $file, $fileName, 'public');

            return response()->json([
                'status' => true,
                'message' => 'Photo uploaded successfully',
                'path' => StorageService::url($directory . '/' . $fileName),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'status' => false,
                'message' => $throwable->getMessage(),
            ], 500);
        }
    }

    public function currentUser(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 200,
            'user' => $user,
            'role' => (int) $user->role,
        ]);
    }

    public function updateLineTemplate(Request $request)
    {
        $data = $request->all();

        $id = $data['id'] ?? null;

        if (!$id) {
            return response()->json([
                'status' => 400,
                'message' => 'Template id is required',
            ]);
        }

        $template = LineTemplate::find($id);

        if (!$template) {
            return response()->json([
                'status' => 404,
                'message' => 'Template not found',
            ]);
        }

        $templateName = $data['template_name'] ?? '';
        $templateType = $data['template_type'] ?? 'length';
        $isGlobal = isset($data['is_global']) ? (int) $data['is_global'] : 0;

        $tradeName = '';

        if (isset($data['trade_name'])) {
            if (is_array($data['trade_name'])) {
                $tradeName = $data['trade_name']['name'] ?? '';
            }

            if (is_string($data['trade_name'])) {
                $tradeDecoded = json_decode($data['trade_name']);
                $tradeName = $tradeDecoded->name ?? $data['trade_name'];
            }
        }

        $formData = $data['formData'] ?? [];

        $template->template_name = $templateName;
        $template->trade_name = $tradeName;
        $template->template_type = $templateType;
        $template->local_db = json_encode($formData);
        $template->is_global = $isGlobal;
        $template->save();

        return response()->json([
            'status' => 200,
            'message' => 'Template updated successfully',
            'template' => $template,
        ]);
    }

    public function saveDrawingTemplate(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();

        $templateId = $data['template_id'] ?? null;

        if (!$templateId) {
            return response()->json([
                'status' => 400,
                'message' => 'template_id is required',
            ]);
        }

        DrawingTemplate::updateOrCreate(
            [
                'user_id' => $user->id,
                'template_id' => $templateId,
            ],
            [
                'template_name' => $data['template_name'] ?? null,
                'color' => $data['color'] ?? '#000000',
            ]
        );

        return response()->json([
            'status' => 200,
            'message' => 'Drawing template saved',
        ]);
    }

    public function getDrawingTemplate($template_id)
    {
        $user = auth()->user();

        $drawingTemplate = DrawingTemplate::where('user_id', $user->id)
            ->where('template_id', $template_id)
            ->first();

        return response()->json([
            'status' => 200,
            'drawing_template' => $drawingTemplate,
        ]);
    }

    public function getLineTemplateById($id)
    {
        try {
            $template = LineTemplate::where('id', $id)
                ->where('template_type', 'length')
                ->first();

            if (!$template) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Template not found',
                    'template' => null,
                ]);
            }

            return response()->json([
                'status' => 200,
                'template' => $template,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'template' => null,
            ]);
        }
    }

    public function templatesLite(Request $request)
    {
        try {
            $user = auth()->user();
            $userId = $user->id;

            $query = LineTemplate::select(
                    'id',
                    'template_name',
                    'trade_name',
                    'template_type',
                    'is_global',
                    'user_id'
                )
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->orWhere('is_global', 1);
                });

            $templates = $query
                ->orderBy('template_type', 'asc')
                ->orderBy('trade_name', 'asc')
                ->orderBy('template_name', 'asc')
                ->get();

            return response()->json([
                'status' => 200,
                'template' => $templates,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'template' => [],
            ], 500);
        }
    }

    public function getAreaTemplateById($id)
    {
        try {
            $template = LineTemplate::where('id', $id)
                ->where('template_type', 'area')
                ->first();

            if (!$template) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Template not found',
                    'template' => null,
                ]);
            }

            return response()->json([
                'status' => 200,
                'template' => $template,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'template' => null,
            ]);
        }
    }

    public function getPerimeterTemplateById($id)
    {
        try {
            $template = LineTemplate::where('id', $id)
                ->where('template_type', 'perimeter')
                ->first();

            if (!$template) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Template not found',
                    'template' => null,
                ]);
            }

            return response()->json([
                'status' => 200,
                'template' => $template,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'template' => null,
            ]);
        }
    }

    public function getOpeningTemplateById($id)
    {
        try {
            $userId = (int) auth()->id();
            $template = LineTemplate::where('id', $id)
                ->where('template_type', 'opening')
                ->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhere('is_global', 1);
                })
                ->first();

            if (!$template) {
                return response()->json([
                    'status' => 404,
                    'template' => null,
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'template' => $template,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'template' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveOpeningTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|integer',
            'template_name' => 'required|string|max:255',
            'formData' => 'required|array',
            'is_global' => 'nullable|boolean',
        ]);

        $userId = (int) auth()->id();
        $formData = $validated['formData'];
        $formData['name'] = $validated['template_name'];
        $formData['type'] = 'opening';

        $template = null;

        if (!empty($validated['template_id'])) {
            $template = LineTemplate::where('id', $validated['template_id'])
                ->where('user_id', $userId)
                ->where('template_type', 'opening')
                ->first();
        }

        if (!$template) {
            $template = LineTemplate::where('user_id', $userId)
                ->where('template_type', 'opening')
                ->where('template_name', $validated['template_name'])
                ->first();
        }

        $template = $template ?? new LineTemplate();

        $template->user_id = $userId;
        $template->template_name = $validated['template_name'];
        $template->trade_name = (string) ($formData['category'] ?? 'Openings');
        $template->template_type = 'opening';
        $template->local_db = json_encode($formData, JSON_UNESCAPED_UNICODE);
        $template->is_global = (int) ($validated['is_global'] ?? 0);
        $template->save();

        return response()->json([
            'status' => 200,
            'template' => $template,
        ]);
    }

}

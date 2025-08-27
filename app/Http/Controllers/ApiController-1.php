<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Casts\MakeEquipment;
use App\Mail\PasswordReset;
use App\Mail\Proposal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Project;
use App\Models\Labor;
use App\Models\Equipment;
use App\Models\Crew;
use App\Models\Document;
use App\Models\Material;
use App\Models\MaterialDivision;
use App\Models\MaterialClass;

use CloudConvert\Laravel\Facades\CloudConvert;
use CloudConvert\Models\Job;
use CloudConvert\Models\Task;
class ApiController extends Controller
{
    public function sendReport(Request $request, $project_id)
    {
        $file = $request->file('file');
        $tmpdir = \public_path("uploads/tmp");
        $hashname = "project-report-".rand(10000, 99999).".pdf";
        $filename = $file->move($tmpdir, $hashname);
        $tmppath = $tmpdir.'/'.$hashname;

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
        } catch(\Exception $err) {
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
                'id'=> $project->id
            ]);
        } catch(\Exception $err) {
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
                'id'=> $project->id
            ]);
        } catch(\Exception $err) {
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
            $totalDayRequired+=$totalDays;
            $totalCost+=round($totalDays * $equipment->cost_per_day, 2);
        }

        return ['reports' => $reports,'totalDays' => $totalDayRequired, 'totalCost' => $totalCost];
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
                $newValue = ['unitPrice' => $prevReport['unitPrice'], 'description' => $report['description'], 'unique_id' => $report['unique_id'],'cost'=> $prevReport['cost'] + $report['cost'], 'name' => $prevReport['name'], 'totalUnits'=>$prevReport['totalUnits'] + $report['totalUnits'], 'class' => $prevReport['class'], 'days'=> $prevReport['days'] + $report['days']];
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
        $reports[] = ['cost'=> round($cost, 2),'unique_id' => $wall_material['unique_id'],'description' => $wall_material['description'],'unitPrice' => $wall_material['prices'], 'name' => $wall_material['name'], 'totalUnits'=> $required_unit, 'class' => $material_class, 'days'=>$day_required];

        $totalCost+=round($cost, 2);
        $totalDays+=$day_required;
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

        return ['total'=> $totalCost, 'reports' => $reports, 'totalDays' => $totalDays];
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
            $reports[] = ['cost'=> $cost,'unique_id' => $material->unique_id,'description' => $material->description,'unitPrice' => $material->prices, 'name' => $name, 'totalUnits'=>$totalUnits, 'class' => $material_class, 'days'=>$day_required];
            $totalCost += $cost;
        }

        return ['total'=> $totalCost, 'reports' => $reports, 'totalDays' => $totalDays];
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

        $reports[] = ['unitPrice' => $main_material->prices, 'description' => $main_material->description, 'unique_id' => $main_material->unique_id, 'cost'=> round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits'=>round($main_material_required, 2), 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost+= $main_material_cost;
        $totalDays += $main_material_days;

        $perimeter_material = Material::where(['user_id' => Auth::id(), 'name' => $used_perimeter_material])->first();
        $perimeter_material_required = ($perimeter_material->length ?? 0) / $required_perimeter;
        $perimeter_cost = (int) $perimeter_material->prices *  $perimeter_material_required;
        $perimeter_class = $perimeter_material->material_class->name;
        $perimeter_material_days = $perimeter_material_required / $perimeter_material->production_rate;
        $totalCost+=$perimeter_cost;
        $totalDays+=$perimeter_material_days;
        $reports[] = ['unitPrice' => $perimeter_material->prices, 'description' => $perimeter_material->description, 'unique_id' => $perimeter_material->unique_id,'cost'=> round($perimeter_cost, 2), 'name' => $perimeter_material->name, 'totalUnits'=>round($perimeter_material_required, 2), 'class' => $perimeter_class, 'days' => round($perimeter_material_days, 2)];

        // $additional_materials = Material::where('user_id', Auth::id())->whereIn('name', $used_additionals)->get();

        foreach ($used_additionals as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            $required = $required_perimeter / (int) $item['qty'];
            $cost = $required * (int) $material->prices;
            $days = $required / $material->production_rate;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['unitPrice' => $material->prices, 'description' => $material->description, 'unique_id' => $material->unique_id,'cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }



        return ['total'=> round($totalCost, 2), 'reports' => $reports, 'totalDays'=>round($totalDays, 2)];
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

        $reports[] = ['cost'=> round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits'=>round($main_material_required, 2).' Sqft', 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost+= $main_material_cost;
        $totalDays += $main_material_days;

        foreach ($used_additionals as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['thickness'] / 12);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }
        foreach ($used_deducts as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['quantity']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }
        foreach ($used_others as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['required']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
        }
        // echo str_replace(['NAN', 'INF'], '0', serialize($reports));die;
        $s_report = unserialize(str_replace([NAN, INF], '0', serialize(['total'=> round($totalCost, 2), 'reports' => $reports, 'totalDays'=>round($totalDays, 2)])));
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

        $reports[] = ['cost'=> round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits'=>round($main_material_required, 2), 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost+= $main_material_cost;
        $totalDays += $main_material_days;

        foreach ($used_additionals as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            if ($material == null) {
                continue;
            }
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0) * ($main_material->height ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['thickness'] / 12);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => $days];
        }
        foreach ($used_deducts as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            if ($material == null) {
                continue;
            }
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['quantity']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => $days];
        }
        foreach ($used_others as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            if ($material == null) {
                continue;
            }
            $required =  (($main_material->length ?? 0) * ($main_material->width ?? 0)) / (int) $requestBody['meta']['area'] * ((int) $item['required']);
            $cost = $required * (int) $material->prices;
            $days = @($required / $material->production_rate) ? @($required / $material->production_rate) : 0;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => $days];
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
        $s_report = unserialize(str_replace([NAN, INF], '0', serialize(['total'=> round($totalCost, 2), 'reports' => $reports, 'totalDays'=>round($totalDays, 2), 'crewReport'=> isset($crewReport) ? $crewReport : null, 'project'=> $project])));
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

        $reports[] = ['cost'=> round($main_material_cost, 2), 'name' => $main_material->name, 'totalUnits'=>round($main_material_required, 2).'', 'class' => $main_material_class, 'days' => round($main_material_days)];
        $totalCost+= $main_material_cost;
        $totalDays += $main_material_days;

        $perimeter_material = Material::where(['user_id' => Auth::id(), 'name' => $used_perimeter_material])->first();
        $perimeter_material_required = ($perimeter_material->length ?? 0) / $required_perimeter;
        $perimeter_cost = (int) $perimeter_material->prices *  $perimeter_material_required;
        $perimeter_class = $perimeter_material->material_class->name;
        $perimeter_material_days = $perimeter_material_required / $perimeter_material->production_rate;
        $totalCost+=$perimeter_cost;
        $totalDays+=$perimeter_material_days;
        $reports[] = ['cost'=> round($perimeter_cost, 2), 'name' => $perimeter_material->name, 'totalUnits'=>round($perimeter_material_required, 2), 'class' => $perimeter_class, 'days' => round($perimeter_material_days, 2)];

        // $additional_materials = Material::where('user_id', Auth::id())->whereIn('name', $used_additionals)->get();

        foreach ($used_additionals as $item) {
            $material = Material::where(['user_id'=> Auth::id(),
            'name' => $item['name']
            ])->first();
            $required = $required_perimeter / (int) $item['qty'];
            $cost = $required * (int) $material->prices;
            $days = $required / $material->production_rate;
            $totalCost+=$cost;
            $totalDays+=$days;
            $reports[] = ['cost'=> round($cost, 2), 'name' => $material->name, 'totalUnits'=>round($required, 2), 'class' => $material->material_class->name, 'days' => round($days, 2)];
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

        return ['total'=> round($totalCost, 2), 'reports' => $reports, 'totalDays'=>round($totalDays, 2), 'crewReport'=> isset($crewReport) ? $crewReport : null, 'project'=> $project];
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
            $reports[] = ['cost'=> $cost, 'name' => $name, 'totalUnits'=>$totalUnits, 'class' => $material_class, 'days'=>$day_required];
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





        return ['total'=> $totalCost, 'reports' => $reports, 'totalDays' => $totalDays, 'crewReport'=> isset($crewReport) ? $crewReport : null, 'project'=> $project];
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
    public function allProjects(){
        $projects = Project::where('user_id', Auth::id())->get();
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
        $project = Project::where('id', $id)->select('name', 'bid_number','bid_date','bid_time')->first();

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
    public function materials()
    {
        $materials = Material::where('user_id', Auth::id())->withCasts([
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
        $cmd = "LD_LIBRARY_PATH=".env('LIB64_PATH').'; pdfinfo';  // Windows

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
    //         $cmd = "LD_LIBRARY_PATH=".env('LIB64_PATH').'; pdftocairo -svg -f '.$i.' -l '.$i.'  "'.$tmppath.'"  "'.$filepath.'"';

    //         print_r($cmd);
    //         $code = \shell_exec($cmd);
    //     }
    //     @unlink($tmppath);
    //     return json_encode([
    //         'status' => 200
    //     ]);
    // }

    // public function plan_upload(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'mimes:svg'
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
    //     $file->move(public_path("uploads/projects/$directory/$path"), $hashname);

    //     return json_encode([
    //         'status' => 200
    //     ]);
    // }
//trying the code that is on live server to convert and upload file
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
//     $pdf = file_get_contents($tmppath); 
//     $totalPages= preg_match_all("/\/Page\W/", $pdf, $dummy); 
//     return json_encode([
//         'totalPages' => $totalPages
//     ]);
//     for ($i = 1; $i <= $totalPages; $i++) {
//         $filepath = $dirpath.str_replace('.pdf', "-$i.svg", $hashname);
//         $cmd = 'pdftocairo -svg -f '.$i.' -l '.$i.'  "'.$tmppath.'"  "'.$filepath.'"';
//         $code = \shell_exec($cmd);
//     }
//     @unlink($tmppath);
//     return json_encode([
//         'status' => 200
//     ]);
// }
// File upload and convert into svg using simple pdf
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
//end file upload function


//upload scan pdf files too is working good

public function plan_upload(Request $request)
{
    $request->validate([
        'file' => 'mimes:pdf'
    ]);

    $project_id = $request->post('project_id');
    $file = $request->file('file');
    $filename = $file->getClientOriginalName();
    $hashname = \str_replace(['/', '#', '?', '\\'], '', $filename);
    $relativePath = $request->post('path');
    $path = \str_replace($filename, '', $relativePath);

    $document = Project::find($project_id)->document;
    if (!$document) {
        $directory = \generate_unique_dir();
        $isSuccess = Document::create([
            'project_id' => $project_id,
            'directory' => $directory
        ]);

        if (!$isSuccess) {
            return \json_encode([
                'status' => 500
            ]);
        }
    } else {
        $directory = $document->directory;
    }
    $dirpath = public_path("uploads/projects/$directory/$path");
    File::ensureDirectoryExists($dirpath);
    $tmpdir = \public_path("uploads/tmp");
    $file->move($tmpdir, $hashname);
    $tmppath = $tmpdir.'/'.$hashname;
    $totalPages = $this->getPDFTotalPages($tmppath);

    // Use pdftotext to attempt text extraction
     $text = shell_exec("pdftotext \"$tmppath\" -");
 
     // Check if the extracted text is empty or mostly unreadable
    if (empty($text) || strlen($text) < 10) {
        $pdfType = 'Scanned PDF';
        for ($i = 1; $i <= $totalPages; $i++) {
            $filepath = $dirpath . str_replace('.pdf', "-$i.svg", $hashname);
            $cmd = 'pdftocairo -svg -f '.$i.' -l '.$i.' -r 50 "'.$tmppath.'" "'.$filepath.'"';
            // $cmd = 'pdftocairo -svg -f ' . $i . ' -l ' . $i . '  "' . $tmppath . '"  "' . $filepath . '"';
            $code = \shell_exec($cmd);
            if ($code === null || $code !== 0) {
                echo "Error: pdftocairo failed for page $i";
                // Handle the error as needed
            } else {
                // Run SVGO to optimize the generated SVG
                $svgoCmd = 'svgo "'.$filepath.'" --output="'.$filepath.'"';
                $svgoCode = \shell_exec($svgoCmd);

                // Check if SVGO succeeded
                if ($svgoCode === null || $svgoCode !== 0) {
                    echo "Error: SVGO failed for page $i";
                    // Handle the error as needed
                }
            }
        }
    } elseif (preg_match('/^\s*\\f+\s*$/', $text)) {
        $pdfType = 'Scanned PDF'; // Text is mostly "\f\f\f"
        for ($i = 1; $i <= $totalPages; $i++) {
            $filepath = $dirpath . str_replace('.pdf', "-$i.svg", $hashname);
            $cmd = 'pdftocairo -svg -f '.$i.' -l '.$i.' -r 50 "'.$tmppath.'" "'.$filepath.'"';
            // $cmd = 'pdftocairo -svg -f ' . $i . ' -l ' . $i . '  "' . $tmppath . '"  "' . $filepath . '"';
            $code = \shell_exec($cmd);
            if ($code === null || $code !== 0) {
                echo "Error: pdftocairo failed for page $i";
                // Handle the error as needed
            } else {
                // Run SVGO to optimize the generated SVG
                $svgoCmd = 'svgo "'.$filepath.'" --output="'.$filepath.'"';
                $svgoCode = \shell_exec($svgoCmd);

                // Check if SVGO succeeded
                if ($svgoCode === null || $svgoCode !== 0) {
                    echo "Error: SVGO failed for page $i";
                    // Handle the error as needed
                }
            }
        }
    } else {
        $pdfType = 'Text-based PDF';
        for ($i = 1; $i <= $totalPages; $i++) {
            $filepath = $dirpath.str_replace('.pdf', "-$i.svg", $hashname);
            $cmd = 'pdftocairo -svg -f '.$i.' -l '.$i.' -r 50 "'.$tmppath.'" "'.$filepath.'"';
            // $cmd = 'pdftocairo -svg -f '.$i.' -l '.$i.'  "'.$tmppath.'"  "'.$filepath.'"';
            $code = \shell_exec($cmd);
            // Check if pdftocairo succeeded
            if ($code === null || $code !== 0) {
                echo "Error: pdftocairo failed for page $i";
                // Handle the error as needed
            } else {
                // Run SVGO to optimize the generated SVG
                $svgoCmd = 'svgo "'.$filepath.'" --output="'.$filepath.'"';
                $svgoCode = \shell_exec($svgoCmd);

                // Check if SVGO succeeded
                if ($svgoCode === null || $svgoCode !== 0) {
                    echo "Error: SVGO failed for page $i";
                    // Handle the error as needed
                }
            }
        }
    }
    @unlink($tmppath);
    return json_encode([
        'status' => 200,
    ]);
}
//end upload scan files


//  Applied cloud convert for converting pdf files into svg

// public function plan_upload(Request $request)
// {
//         $request->validate([
//             'file' => 'mimes:pdf'
//         ]);
    
//         $project_id = $request->post('project_id');
//         $file = $request->file('file');
//         // foreach ($files as $file) {
//         //     dd($file);
//         // }
//         // dd('$filesss');
//         $filename = $file->getClientOriginalName();
//         $hashname = \str_replace(['/', '#', '?', '\\'], '', $filename);
//         $relativePath = $request->post('path');
//         $path = \str_replace($filename, '', $relativePath);
//         $document = Project::find($project_id)->document;
//         if (!$document) {
//             $directory = \generate_unique_dir();
//             $isSuccess = Document::create([
//                 'project_id' => $project_id,
//                 'directory' => $directory
//             ]);
    
//             if (!$isSuccess) {
//                 return \json_encode([
//                     'status' => 500
//                 ]);
//             }
//         } else {
//             $directory = $document->directory;
//         }
//         $dirpath = public_path("uploads/projects/$directory/$path");
//         File::ensureDirectoryExists($dirpath);
//         $tmpdir = \public_path("uploads/tmp");
//         $file->move($tmpdir, $hashname);
//         $tmppath = $tmpdir.'/'.$hashname;
//         $pdf = file_get_contents($tmppath); 
//         $totalPages= preg_match_all("/\/Page\W/", $pdf, $dummy); 
//         $pdfFilePath = 'http://127.0.0.1:8000/uploads/tmp/'.rawurlencode($hashname);

//         $inputFilePath = $pdfFilePath;
//         // dd($inputFilePath);
//         $outputDirectory = public_path("uploads/projects/$directory/");
//         // $outputFilePath = $outputDirectory . "converted-svg.svg";
        
//         // Ensure that the output directory exists, or create it
//         if (!file_exists($outputDirectory)) {
//             mkdir($outputDirectory, 0755, true);
//         }
//         for ($page = 1; $page <= $totalPages; $page++) {
//             $outputFileName = $hashname.'-'.$page . '.svg';
//             $outputFilePath = $outputDirectory . $outputFileName;
    
//             $job = CloudConvert::jobs()->create(
//                 (new Job())
//                     ->setTag('myjob-pdf-to-svg')
//                     ->addTask(
//                         (new Task('import/url', 'import-my-file'))
//                             ->set('url', $inputFilePath)
//                     )
//                     ->addTask(
//                         (new Task('convert', 'convert-my-file-' . $page))
//                             ->set('input', 'import-my-file')
//                             ->set('output_format', 'svg')
//                             ->set('page_range', $page . '-' . $page)
//                     )
//                     ->addTask(
//                         (new Task('export/url', 'export-my-file-' . $page))
//                             ->set('input', 'convert-my-file-' . $page)
//                     )
//             );
    
//             $job = CloudConvert::jobs()->wait($job);
//             $tasks = $job->getTasks();
//             $firstTask = $tasks[0];
//             $extractedFiles = [];
//             if ($firstTask->getResult()) {
//                 // Access the result of the first task
//                 $result = $firstTask->getResult();
//                 foreach ($result->files as $file) {
//                     $svgContent = file_get_contents($file->url);
//                     $outputFilePaths = $outputDirectory . $file->filename;
//                     file_put_contents($outputFilePaths, $svgContent);
//                     // Add to the extracted files array
//                     $extractedFiles[] = [
//                         'filename' => $file->filename,
//                         'url' => $file->url,
//                     ];
//                 }
//             }
//             // dd($extractedFiles);
//         }

//         @unlink($tmppath);
//         return json_encode([
//             'status' => 200
//         ]);
// }
//end upload scan files

    public function project_plans($id)
    {
        $document = Project::find($id)->document;
        if (!$document) {
            return [];
        }
        $directory = $document->directory;
        $folder = "projects/$directory/";
        $files = Storage::allFiles($folder);

        return $files;
    }
    public function delete_all_files($id)
    {
        $document = Project::find($id)->document;
        if (!$document) {
            return \json_encode([
                'status' => 500,
                'message' => 'No files to delete'
            ]);
        }
        $directory = $document->directory;
        $relativePath = "uploads/projects/$directory";
        File::cleanDirectory(public_path($relativePath));

        return json_encode([
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
    public function current_location(Request $request) {
        $currentpath =  $request->all();
        $fileLocation = $currentpath['fileLocation'];
        $windowLocation = $currentpath['windowLocation'];
        $user = User::find(Auth::id());
        if ($user) {
            $user->current_location_file=$fileLocation;
            $user->current_location=$windowLocation;
            $user->update();
        }
        return [
            'status'=> true
        ];
        
    }
    public function sync_local_db(Request $request)
    {
        $data =  $request->json()->all();
        $user = User::find(Auth::id())->update([
            'local_db' =>  $data
        ]);

        return [
            'status'=> true
        ];
    }
    public function get_local_db()
    {
        $user = User::find(Auth::id());

        return [
            'data' => $user->local_db
        ];
    }

    function createProject(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'bid_date' => 'required|date',
        ]);
        $user_id = Auth::id();

        $project = new Project();
        $project->name = $validatedData['name'];
        $project->bid_date = $validatedData['bid_date'];
        $project->user_id = $user_id;
        $project->save();
        return json_encode([
            'status' => 200,
            'url' => route('project.measurement', ['id' => $project->id]),
        ]);
    }

    function projectMeasurementData(Request $request) {
        $projectName= $request->project_name;
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
        }else{
            return json_encode([
                'status' => 400,
            ]);
        }
    }

    function getMeasurementData($name) {
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
        }else{
            return json_encode([
                'status' => 400,
            ]);
        }
    }
    public function logout() {
        // dd(Auth::id());
        Auth::logout();
        return json_encode([
            'status' => 200,
        ]);
    }
    
}

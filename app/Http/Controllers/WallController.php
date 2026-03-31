<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LineTemplate;
use App\Models\Wall;
use App\Models\Project;
use Exception;
use PhpParser\node\Expr\Cast\Object_;
use Illuminate\Support\Facades\DB;

class WallController extends Controller
{
    public $totalsDatasFinal = [];
    function recalculate($wall_id)
    {
        /*  $lineTemplate = LineTemplate::find($project_id);
        $json = json_decode($lineTemplate->local_db);

        $json->wall_length = 721.72;
 */
        $wall = Wall::find($wall_id);
        
        $project = Project::where('id', $wall->project_id)->select('tax', 'oh', 'profit', 'weather')->first();
        $wall->project = $project;
        //dd($wall);

        if ($wall->type == "length") {

            $result = $this->processWallCalculations($wall);
        }
        if ($wall->type == "area") {
            //var_dump($wall);
            $result = $this->processArea($wall);
        }
        if ($wall->type == "perimeter") {
            //var_dump($wall);
            $result = $this->processPerimeter($wall);
        }
        if ($wall->type == "opening") {

            $result = $this->processOpening($wall);
        }

        // print("<pre>" . print_r($result, true) . "</pre>");
        $wall->formData=null;
        //dd($result);
        return $result;
    }
    function processOpening($data)
    {

        $data = json_decode($data->formData);
       
        $data->top_elevation=0;
        $data->material_sq_ft=0;
        $data->sq_area= 0; // Numeric
        $data->total_units= 0; // Numeric
        $data->total= 0; // Numeric
        $data->total_sq_ft= 0; // Numeric
        $data->total_units_opening= 0; // Numeric
        $data->total_area= 0; // Numeric
        $data->total_cy= 0; // Numeric
        $data->header_reinforcing= 0; // Select (Material)
        $data->total_reinforcing= 0; // Numeric
        
        $data->total_materials= 0; // Numeric
        $data->total_units= 0; // Numeric
        $data->total_length= 0; // Numeric
        $data->jamb_total_area= 0; // Numeric
        $data->jamb_total_units= 0; // Numeric
        $data->total_cubic_area= 0; // Numeric
        $data->area_cubic_yards= 0; // Numeric
        $data->total_cy_jamb= 0; // Numeric
        $data->reinforcing_spacing= 0; // Numeric
        $data->total_spaces= 0; // Numeric
        $data->total_lf= 0; // Numeric
        $data->total_material_units= 0; // Numeric
        $data->sq_area_wall= 0; // Numeric
        $data->total_grout_fill_cy= 0; // Numeric
        
        $data->other_fill= 0; // Select (Material)
        $data->total_sq_area= 0; // Numeric
        $data->total_cy_other_fill= 0; // Numeric
         
       


        if (isset($data->totalsDatas)) {
            $materialesAgrupados = $this->agruparMaterialesPorId($data->totalsDatas);

            //print_r($data->totalsDatas);
            //print_r($materialesAgrupados);
            $data->totales_html = $this->generarTablaHtml($materialesAgrupados);
        }
        else
        {
            $data->totales_html ="";
        }

        $this->handleChangeadjustmentDatas($data);
        return  $data;
    }
    function processPerimeter($data)
    {
        $project = $data->project;
        $project_id = $data->project_id;
        $data = json_decode($data->formData);
       
        $perimeterFieldsFinal = [array( 
            "perimeter"=> "", 
            "material"=> "",
            "materialQty"=> "", 
            "totalLf"=> "", 
            "totalUnits"=> "" 
         )];
       
        $perimeterFields = $data->perimeterFields ?? [];
        
        if(isset($data->perimeterFields))
        {
            $perimeterFieldsFinal = [];

        }

        foreach ($perimeterFields as $index  =>  $additionalData) {
            $total_measuring = 0;




            try {
                 
                $selectedMaterial = json_decode($additionalData->material);
                if ($selectedMaterial != null) {


                 

                    $additionalData->totalLf=$additionalData->perimeter;
                    //$additionalData->totalUnits= round(($additionalData->perimeter*$additionalData->materialQty)/$selectedMaterial->unit_measure_value,2);
                    if (!empty($selectedMaterial->unit_measure_value)) {
                        $additionalData->totalUnits = round(((float)$additionalData->perimeter * (float)$additionalData->materialQty) / $selectedMaterial->unit_measure_value, 2);
                    } else {
                        $additionalData->totalUnits = 0;
                    }
                     
                    $measuring = round(((float)$additionalData->perimeter * (float)$additionalData->materialQty), 2);


                    //Agregar material a la cuenta
                    $Agregarmaterial = new Total_Material();
                    $Agregarmaterial->id_material = $selectedMaterial->id;
                    $Agregarmaterial->material = $selectedMaterial;
                    $Agregarmaterial->measuring = $measuring;
                    $Agregarmaterial->op_unit   = 'lf';
                    $Agregarmaterial->units = $additionalData->totalUnits;
                    $this->addtotalDatas($Agregarmaterial, $data);
                }

                $perimeterFieldsFinal[] = $additionalData;
             } catch (\Throwable $thx) {
                log_message('error', 'processPerimeter error: ' . $ex->getMessage());
             }
        }
        $data->perimeterFields = $perimeterFieldsFinal;


        if (isset($data->totalsDatas)) {
            $materialesAgrupados = $this->agruparMaterialesPorId($data->totalsDatas);

            $crew = $this->addCrew($project_id);
            $trade = json_decode($data->trade);
            $materialesAgrupados = $this->Calcula_totalDatas1($materialesAgrupados, $project, $crew, $trade->name ?? '', $data->name ?? '');

            //print_r($data->totalsDatas);
            //print_r($materialesAgrupados);
            //$data->totales_html = $this->generarTablaHtml($materialesAgrupados);
            //$material = json_decode($data->wall_material);
            $data->materialesAgrupados = $materialesAgrupados;

            $data->totales_html = $this->generarTablaHtml($materialesAgrupados, [
                'report_id'      => 'perimeter_' . ($data->wall_id ?? '') . '_' . time(),
                'report_type'    => 'PERIMETER',
                'trade_name'    => $data->trade_name ?? '',
                'trade'         => $trade->name ?? '',
                'scope_label'   => $data->template_name ?? ($data->name ?? ''),
                //'material_label' => ($material->unique_id . " " . $material->name  ?? ''),
                'generated_at'   => date('Y-m-d H:i'),
            ]);
        }
        else
        {
            $data->totales_html ="";
        }

        $this->handleChangeadjustmentDatas($data);
        return  $data;
    }
    
    function processArea($data)
    {
        //var_dump($data->formData);
        $project = $data->project;
        $project_id = $data->project_id;
        $data = isset($data->formData) ? json_decode($data->formData) : (object)[];
        //var_dump($data);

        // Si estÃ¡ guardada el Ã¡rea original, la preservamos en la variable
        if (isset($data->wall_total_area_original)) {
            $area_original = (float)$data->wall_total_area_original;
        }

        // 1) Si existe pitch vÃ¡lido â†’ aplicar
        if (isset($data->pitch) && (float)$data->pitch > 0) {

            // Si no se ha guardado aÃºn el Ã¡rea original, la guardamos
            if (!isset($data->wall_total_area_original)) {
                $data->wall_total_area_original = $data->wall_total_area;
            }

            $data->wall_total_area = $data->wall_total_area * (float)$data->pitch;
        }

        // 2) Si NO hay pitch pero SÃ hay original â†’ restaurar
        if ((!isset($data->pitch) || $data->pitch == "" || $data->pitch == 0)
            && isset($data->wall_total_area_original)) {

            $data->wall_total_area = $data->wall_total_area_original;
        }

        if (is_numeric($data->Area_thickness) && (float)$data->Area_thickness > 0) {
            $data->area_cubic_ft = (float)$data->Area_thickness * (float)$data->wall_total_area;
        } else {
            $data->area_cubic_ft = $data->wall_total_area;
        }

        
        
        $data->underlay_sq_ft = $data->wall_total_area;

        //rise_drop
        
        if (is_string($data->wall_material)) {
            $selectedMaterial = json_decode($data->wall_material);
            
        }else {
            $selectedMaterial = $data->wall_material;
        }
        if ($selectedMaterial != null) {
            
            $data->Total_units = round($data->area_cubic_ft / $selectedMaterial->unit_measure_value, 2);

            $data->rise_drop_area_added = $data->rise_drop_rise * $data->wall_total_perimeter;
            if (is_numeric($data->rise_drop_thickness) && (float)$data->rise_drop_thickness > 0) {
                $data->rise_drop_total = $data->rise_drop_area_added * $data->rise_drop_thickness;
            } else {
                $data->rise_drop_total = $data->rise_drop_area_added;
            }
            $data->rise_drop_total_unit =  round($data->rise_drop_total / $selectedMaterial->unit_measure_value, 2);

            $total_measuring = $data->area_cubic_ft + $data->rise_drop_total_unit;

            $total_units = $data->Total_units;

            //Agregar material a la cuenta
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $total_measuring;
            $Agregarmaterial->op_unit   = 'sqft';
            $Agregarmaterial->units = $total_units;
            $this->addtotalDatas($Agregarmaterial, $data);
        }

        //underlay
        if (is_string($data->underlay_material)) {
            $selectedMaterial = json_decode($data->underlay_material);
            
        }else {
            $selectedMaterial = $data->underlay_material;
        }
        if ($selectedMaterial != null) {
            if (is_numeric($data->underlay_thickness) && (float)$data->underlay_thickness > 0) {
                $data->underlay_total  = $data->underlay_sq_ft * $data->underlay_thickness;
            }else{
                $data->underlay_total  = $data->underlay_sq_ft;
            }
            $total_units = $data->underlay_total_unit  =  round($data->underlay_total / $selectedMaterial->unit_measure_value, 2);
            $total_measuring = $data->underlay_total;


            //Agregar material a la cuenta
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $total_measuring;
            $Agregarmaterial->op_unit   = 'sqft';
            $Agregarmaterial->units = $total_units;
            $this->addtotalDatas($Agregarmaterial, $data);
        }


        //$additionalMaterials = (object)$data->additionalMaterials;
        $additionalMaterials = $data->additionalMaterials ?? [];

        $additionalDatasFinal = [];

        foreach ($additionalMaterials as $index  =>  $additionalData) {
            $total_measuring = 0;
            $additionalData = (object)$additionalData;
            // $additionalDatas->additionalDatas[$index]=$additionalData;
            // echo "index " . $index . "<br>";
            // echo "additionalData " . json_encode($additionalData) . "<br>";



            $additional_material = $additionalData->material ?? null;

            $selectedMaterial = null;

            // âœ… Si viene como string JSON â†’ decodifica
            if (is_string($additional_material) && $additional_material !== '') {
                $selectedMaterial = json_decode($additional_material);
            }

            // âœ… Si ya viene como objeto (stdClass) â†’ Ãºsalo tal cual
            if (is_object($additional_material)) {
                $selectedMaterial = $additional_material;
            }

            // âœ… Si viene como array â†’ conviÃ©rtelo a objeto
            if (is_array($additional_material)) {
                $selectedMaterial = (object)$additional_material;
            }
            if ($selectedMaterial != null) {


                $thickness = isset($additionalData->thickness) 
                                ? (float)$additionalData->thickness 
                                : 0;
                $cubicFt     = (float) $data->wall_total_area; // o lo que defina tu cubicFt
                $unitMeasure = (float) $selectedMaterial->unit_measure_value;

                $additionalData->cubicFt = $cubicFt;

                //$additionalData->cubicFt = $data->wall_total_area * $thickness;
                if (is_numeric($thickness) && (float)$thickness > 0) {
                    $total_measuring1 = ($cubicFt * $thickness) / ($unitMeasure > 0 ? $unitMeasure : 1);
                    $total_measuring = $cubicFt * $thickness;
                }else{
                    $total_measuring1 = $cubicFt / ($unitMeasure > 0 ? $unitMeasure : 1);
                    $total_measuring = $cubicFt;
                }
                $total_units = $additionalData->totalUnits = round($total_measuring1, 2);
                



                //Agregar material a la cuenta
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $total_measuring;
                $Agregarmaterial->op_unit   = 'sqft';
                $Agregarmaterial->units = $total_units;
                $this->addtotalDatas($Agregarmaterial, $data);
            }
            $additionalDatasFinal[] = $additionalData;
        }
        $data->additionalMaterials = $additionalDatasFinal;

        //$material_per_sq_ft = (object)$data->material_per_sq_ft;
        $additionalDatasFinal = [];
        $material_per_sq_ft  = $data->material_per_sq_ft ?? [];


        foreach ($material_per_sq_ft as $index  =>  $additionalData) {
            $total_measuring = 0;




            try {
                $additional_material = $additionalData;
                $selectedMaterial = $additional_material;
                if (is_string($selectedMaterial)) {
                    $selectedMaterial = json_decode($selectedMaterial);
                }
                if ($selectedMaterial != null) {


                    //$qty = $data->{"quantity_per_sq_ft[" . $index . "]"};
                    $qty = $data->quantity_per_sq_ft[$index] ?? 0;

                    $total = $data->wall_total_area * $qty;
                    $total_measuring = round($total, 2);
                    
                    if (!isset($data->Totalquantity_per_sq_ft) || $data->Totalquantity_per_sq_ft === null) {
                        $data->Totalquantity_per_sq_ft = [];
                    }

                    // Si ya viene como array, ok. Si viene como stdClass, pÃ¡salo a array.
                    if (is_object($data->Totalquantity_per_sq_ft)) {
                        $data->Totalquantity_per_sq_ft = (array) $data->Totalquantity_per_sq_ft;
                    }

                    // Si viene raro (string), lo reseteas
                    if (!is_array($data->Totalquantity_per_sq_ft)) {
                        $data->Totalquantity_per_sq_ft = [];
                    }
                    $data->Totalquantity_per_sq_ft[$index] = $total_measuring;
                    $total_units = $total_measuring / $selectedMaterial->unit_measure_value;

                    //Agregar material a la cuenta
                    $Agregarmaterial = new Total_Material();
                    $Agregarmaterial->id_material = $selectedMaterial->id;
                    $Agregarmaterial->material = $selectedMaterial;
                    $Agregarmaterial->measuring = $total_measuring;
                    $Agregarmaterial->op_unit   = 'sqft';
                    $Agregarmaterial->units = $total_units;
                    $this->addtotalDatas($Agregarmaterial, $data);
                }

                $additionalDatasFinal[] = $additionalData;
            } catch (\Throwable $thx) {
            }
        }
        $data->material_per_sq_ft = $additionalDatasFinal;


        if (isset($data->totalsDatas)) {
            $materialesAgrupados = $this->agruparMaterialesPorId($data->totalsDatas);

            //print_r($data->totalsDatas);
            //print_r($materialesAgrupados);
            $crew = $this->addCrew($project_id);
            $trade = json_decode($data->trade);
            $material = json_decode($data->wall_material);
            $materialesAgrupados = $this->Calcula_totalDatas1($materialesAgrupados, $project, $crew, $trade->name ?? '', $data->name ?? '');
            //$data->totales_html = $this->generarTablaHtml($materialesAgrupados);
            $data->materialesAgrupados = $materialesAgrupados;

            $data->totales_html = $this->generarTablaHtml($materialesAgrupados, [
                'report_id'      => 'area_' . ($data->wall_id ?? '') . '_' . time(),
                'report_type'    => 'AREA',
                'trade_name'    => $data->trade_name ?? '',
                'trade'         => $trade->name ?? '',
                'scope_label'   => $data->template_name ?? ($data->name ?? ''),
                'material_label'=> ($material->unique_id ?? '') . ' ' . ($material->name ?? ''),
                'generated_at'   => date('Y-m-d H:i'),
            ]);
        }
        


        $this->handleChangeadjustmentDatas($data);
        //dd($data);
        return  $data;
    }
    function processWallCalculations($data)
    {
        if (!isset($data->totalsDatas) || !is_array($data->totalsDatas)) {
            $data->totalsDatas = [];
        }

        $this->calculaWallMaterial($data);
        $this->calculaBloque1($data);
        $this->calculaBloque2($data);
        $this->calculaBandMaterial($data);
        $this->calculaBloque4($data);
        $this->calculaBloque5($data);
        $this->handleUseCourse($data);
        $this->handleChangeAdditionalDatas($data);
        $this->handleChangeadjustmentDatas($data);
        $this->Calcula_totalDatas($data);
        $materialesAgrupados = $this->agruparMaterialesPorId($data->totalsDatas);

        $crew = $this->addCrew($data->project_id);
        $trade = json_decode($data->trade);
        $material = json_decode($data->wall_material);
        $materialesAgrupados = $this->Calcula_totalDatas1($materialesAgrupados, $data->project, $crew, $trade->name ?? '', $data->name ?? '');

        //print_r($data->totalsDatas);
        //print_r($materialesAgrupados);
        //$data->totales_html = $this->generarTablaHtml($materialesAgrupados);

        $data->materialesAgrupados = $materialesAgrupados;
        
        $data->totales_html = $this->generarTablaHtml($materialesAgrupados, [
            'report_id'      => 'length_' . ($data->wall_id ?? '') . '_' . time(),
            'report_type'    => 'LENGTH',
            'trade_name'    => $data->trade_name ?? '',
            'trade'         => $trade->name ?? '',
            'scope_label'   => $data->template_name ?? ($data->name ?? ''),
            'material_label'=> ($material->unique_id ?? '') . ' ' . ($material->name ?? ''),
            'generated_at'   => date('Y-m-d H:i'),
        ]);


        return  $data;
    }

    #region calcula material   

    private function resolve_measuring_unit(string $type): string
    {
        $map = [
            // Wall / base
            'WALL_LENGTH'     => 'ft',
            'WALL_AREA'       => 'sq_ft',
            'WALL_VOLUME_FT3' => 'cu_ft',
            'WALL_VOLUME_CY'  => 'cy',

            // Bloque 1
            'COPING'          => 'ft',
            'TOP_WALL'        => 'ft',
            'ANCHORS'         => 'ea',

            // Bloque 4
            'CONTROL_JOINT'   => 'ft',     // si tu cálculo es lineal
            'CAULKING'        => 'ft',     // si tu cálculo es lineal
            'HALF_BLOCK'      => 'sq_ft',  // si tu cálculo es área

            // Bloque 5
            'REBAR_LF'        => 'ft',
            'REBAR_TON'       => 'ton',
            'POSITIONERS'     => 'ea',     // o ft si aplica en tu lógica
            'REMAINING'       => 'sq_ft',  // o cu_ft/cy según tu cálculo real

            // Additional datas
            'LINEAL'          => 'ft',
            'SPACING_FT'      => 'ft',
            'PER_SQ_FT'       => 'sq_ft',
            'PR_AREA'         => 'sq_ft',
            'TOP_BOTTOM'      => 'ft',
            'QUANTITY'        => 'ea',

            // Perimeter/Area tool
            'PERIMETER'       => 'ft',
            'AREA'            => 'sq_ft',

            // Adjustment
            'ADJUSTMENT'      => 'sq_ft',  // ajusta si tu adjustment es ft, cy, etc.
        ];

        return $map[$type] ?? '';
    }

    private function push_total_material(
        &$formData,
        $materialObj,
        float $measuring,
        string $measuring_unit,
        ?float $units = null,
        ?string $units_unit = null,
        bool $principal = false
    ) {
        if ($materialObj == null || !isset($materialObj->id)) {
            return;
        }

        if (!isset($formData->totalsDatas) || !is_array($formData->totalsDatas)) {
            $formData->totalsDatas = [];
        }

        $m = new Total_Material();
        $m->id_material    = $materialObj->id;
        $m->material       = $materialObj;

        $m->measuring      = (float)$measuring;
        $m->measuring_unit = (string)$measuring_unit;

        if ($units !== null) {
            $m->units = (float)$units;
        }
        if ($units_unit !== null) {
            $m->units_unit = (string)$units_unit;
        }

        $m->principal = $principal;

        $this->addtotalDatas($m, $formData);
    }


    public function calculaWallMaterial(&$updatedFormData)
    {
        // echo "calculaWallMaterial<br>";
        // Ensure $updatedFormData is an stdClass object
        if (is_array($updatedFormData)) {
            $updatedFormData = (object)$updatedFormData;
        }
        if ($updatedFormData->wall_material != null) {
            // Handle changes for main material
            $wall_material = $updatedFormData->wall_material;
            $selectedMaterial = json_decode($wall_material); // Parse the selected material

            // Access the height, width, and length properties of the selected material object
            $materialLength = $selectedMaterial->length;
            $materialHeight = $selectedMaterial->height;
            $materialWidth = $selectedMaterial->width;

            // Calculate units
            $calculateUnit = $this->calculateWallUnit($materialHeight, $materialLength);
            $calculateSqUnit = $this->calculateWallSqUnit($calculateUnit);
            $calculateCubicUnit = $this->calculateWallCubicUnit($materialLength, $materialHeight, $materialWidth);

            // Update the form data
            $updatedFormData->material_height = $materialHeight;
            $updatedFormData->material_width = $materialWidth;
            $updatedFormData->material_length = $materialLength;
            $updatedFormData->wall_material_unit = $calculateUnit;
            $updatedFormData->wall_material_square_unit = $calculateSqUnit;
            $updatedFormData->wall_material_cubic_unit = $calculateCubicUnit;
        }
        return $updatedFormData;
    }

    #endregion calcula material


    public function calculaBloque1(&$updatedFormData)
    {
        $calculatedEffectiveFoundationHeight = $this->calculateFoundationHeight($updatedFormData);
        $calculatedTotalWallHeight           = $this->calculatedWallHeight($updatedFormData, $calculatedEffectiveFoundationHeight);

        $calculateTotalWallLength  = $this->calculationWallLength($updatedFormData);
        $calculateTotalSquareArea  = $this->calculationSquareArea($calculatedTotalWallHeight, $calculateTotalWallLength);
        $calculateTotalCubicArea   = $this->calculationCubicArea($updatedFormData, $calculateTotalSquareArea);
        $calculateAreaCubicYards   = $this->calculationCubicYards($updatedFormData, $calculateTotalCubicArea);
        $calculateWallSquareUnits  = $this->calculationWallSquareUnit($updatedFormData, $calculateTotalSquareArea);

        // ====== COPING ======
        $calculateCopingTotals     = $this->calculationCopingTotal($updatedFormData);
        $calculateCopingTotalUnits = $this->calculationCopingTotalUnit($updatedFormData, $calculateCopingTotals);

        // ====== GUARDAR CAMPOS EN FORM DATA ======
        $updatedFormData->effective_foundation_height = $calculatedEffectiveFoundationHeight;
        $updatedFormData->total_wall_height           = $calculatedTotalWallHeight;
        $updatedFormData->total_wall_length           = $calculateTotalWallLength;
        $updatedFormData->total_square_area           = $calculateTotalSquareArea;

        $updatedFormData->total_cubic_area            = $calculateTotalCubicArea;
        $updatedFormData->area_cubic_yards            = $calculateAreaCubicYards;
        $updatedFormData->wall_square_units           = $calculateWallSquareUnits;

        // Wall coping material
        $updatedFormData->coping_material_total       = $calculateCopingTotals;
        $updatedFormData->coping_material_total_units = $calculateCopingTotalUnits;

        // ====== MATERIAL PRINCIPAL (WALL MATERIAL) ======
        $wall_material_obj = null;
        if (isset($updatedFormData->wall_material)) {
            $wall_material_obj = json_decode($updatedFormData->wall_material);
        }

        if ($wall_material_obj != null) {
            $material_principal = new Total_Material();
            $material_principal->id_material = $wall_material_obj->id;
            $material_principal->material    = $wall_material_obj;

            // Measuring = base (ft²)
            $material_principal->measuring   = (float)$calculateTotalSquareArea;
            $material_principal->units = (float)$calculateWallSquareUnits;
            $material_principal->op_unit   = 'sqft';

            $material_principal->principal  = true;
            $this->addtotalDatas($material_principal, $updatedFormData);
        }

        // ====== COPING MATERIAL (MEASURING + UNITS) ======
        $coping_material_obj = null;
        if (isset($updatedFormData->coping_material)) {
            $coping_material_obj = json_decode($updatedFormData->coping_material);
        }

        if ($coping_material_obj != null) {
            $material_coping = new Total_Material();
            $material_coping->id_material = $coping_material_obj->id;
            $material_coping->material    = $coping_material_obj;

            // ✅ Measuring = total lineal calculado (ft)
            $material_coping->measuring   = (float)$updatedFormData->coping_material_total;
            $material_coping->op_unit   = 'lf';

            // ✅ Units = total units calculadas para el material
            $material_coping->units       = (float)$updatedFormData->coping_material_total_units;

            $this->addtotalDatas($material_coping, $updatedFormData);
        }

        // ====== ANCHOR SPACES (solo matemático) ======
        $anchor_spacing           = isset($updatedFormData->anchor_spacing) ? (float)$updatedFormData->anchor_spacing : 0;
        $anchor_additional_spaces = isset($updatedFormData->anchor_additional_spaces) ? (float)$updatedFormData->anchor_additional_spaces : 0;

        // Si hay spacing válido, calcula spaces (matemático)
        if ($anchor_spacing > 0) {
            $updatedFormData->anchor_total_spaces = ($updatedFormData->total_wall_length / $anchor_spacing) + $anchor_additional_spaces;
        }

        // Total anchors
        $updatedFormData->anchor_total = $this->calculationTotalAnchors($updatedFormData);

        // Agregar anchor material (MEASURING + UNITS)
        $anchor_material_obj = null;
        if (isset($updatedFormData->anchor_material)) {
            $anchor_material_obj = json_decode($updatedFormData->anchor_material);
        }

        if ($anchor_material_obj != null) {
            $material_anchor = new Total_Material();
            $material_anchor->id_material = $anchor_material_obj->id;
            $material_anchor->material    = $anchor_material_obj;

            // ✅ Measuring = anchor_total (conteo base)
            $material_anchor->measuring   = (float)$updatedFormData->anchor_total;
            $material_anchor->op_unit   = '';

            // ✅ Units = matemáticamente igual (si luego quieres cajas, aquí aplicarías unit_measure_value)
            $material_anchor->units       = (float)$updatedFormData->anchor_total;

            $this->addtotalDatas($material_anchor, $updatedFormData);
        }

        // ====== TOP WALL MATERIAL (BRICK / TOP WALL) ======
        $top_wall_material_obj = null;
        if (isset($updatedFormData->top_wall_material)) {
            $top_wall_material_obj = json_decode($updatedFormData->top_wall_material);
        }

        $updatedFormData->total_anchor_coping       = $this->calculationTotalAnchorCoping($updatedFormData);
        $updatedFormData->total_anchor_coping_units = $this->calculationTotalAnchorCopingUnits(
            $top_wall_material_obj,
            $updatedFormData->total_anchor_coping
        );

        // Agregar top wall material (MEASURING + UNITS)
        if ($top_wall_material_obj != null) {
            $material_top_wall = new Total_Material();
            $material_top_wall->id_material = $top_wall_material_obj->id;
            $material_top_wall->material    = $top_wall_material_obj;

            // ✅ Measuring = base lineal (ft) (NO units)
            $material_top_wall->measuring   = (float)$updatedFormData->total_anchor_coping;
            $material_top_wall->op_unit   = 'lf';

            // ✅ Units = lo convertido (p.ej. 4.39)
            $material_top_wall->units       = (float)$updatedFormData->total_anchor_coping_units;

            $this->addtotalDatas($material_top_wall, $updatedFormData);
        }

        return $updatedFormData;
    }


    public function calculaBloque5(&$updatedFormData)
    {
        // echo "calculaBloque5<br>";
        // Calculate functions
        
        if (is_array($updatedFormData)) {
            $updatedFormData = (object)$updatedFormData;
        }

        // ✅ Asegurar totalsDatas
        if (!isset($updatedFormData->totalsDatas) || !is_array($updatedFormData->totalsDatas)) {
            $updatedFormData->totalsDatas = [];
        }

        // =========================================================
        // 1) Calcular primero Fill Mat Per CY (antes de usarlo)
        // =========================================================
        $calculateFillMatPerCy = $this->calculateFillMatPerCys($updatedFormData);

        // Si el usuario metió manuality, se respeta; si no, usa el calculado
        $manual_fill = isset($updatedFormData->sq_fill_mat_per_cy_manuality) ? (float)$updatedFormData->sq_fill_mat_per_cy_manuality : 0;
        $updatedFormData->sq_fill_mat_per_cy = ($manual_fill > 0) ? $manual_fill : (float)$calculateFillMatPerCy;

        // =========================================================
        // 2) Cálculos del bloque
        // =========================================================
        $calculateSpacesFilled         = $this->calculateTotalSpacesFilled($updatedFormData);
        $calculateTotalLift            = $this->calculateTotalLifts($updatedFormData);
        $calculateRebarLf              = $this->calculateRebarLfs($updatedFormData, $calculateTotalLift);
        $calculateVericalRebarTotal    = $this->calculateVericalRebarTotals($updatedFormData, $calculateSpacesFilled, $calculateRebarLf);

        $calculateRebarTon             = $this->calculateRebarTons($updatedFormData, $calculateVericalRebarTotal);
        $calculateRebarPerTon          = $this->calculateRebarPerTons($updatedFormData->grout_fill_material);

        $calculatePostionPerTotal      = $this->calculatePostionPerTotals($updatedFormData);
        $calculatePostionOtherTotal    = $this->calculatePostionOtherTotals($calculateSpacesFilled, $calculatePostionPerTotal);

        $calculateAreaGrouted          = $this->calculateAreaGrouteds($updatedFormData, $calculateSpacesFilled);
        $calculateRemainingArea        = $this->calculateRemainingAreas($updatedFormData, $calculateAreaGrouted);

        $calculateGroutMaterial        = $this->calculateGroutMaterials($updatedFormData, $calculateAreaGrouted);
        $calculateRemainingMaterial    = $this->calculateRemainingMaterials($updatedFormData, $calculateRemainingArea);

        // =========================================================
        // 3) Guardar en form data (igual que tu flujo)
        // =========================================================
        $updatedFormData->total_spaces_filled              = $calculateSpacesFilled;
        $updatedFormData->total_lifts                      = $calculateTotalLift;
        $updatedFormData->rebar_lf_pr_space                = $calculateRebarLf;
        $updatedFormData->vertical_rebar_total             = $calculateVericalRebarTotal;

        $updatedFormData->lft_rebar_per_ton                = $calculateRebarPerTon;
        $updatedFormData->vertical_total_rebar_tons        = $calculateRebarTon;

        $updatedFormData->vertical_postioner_per_total     = $calculatePostionPerTotal;
        $updatedFormData->vertical_postioner_other_total   = $calculatePostionOtherTotal;

        $updatedFormData->vertical_grouted_area            = $calculateAreaGrouted;
        $updatedFormData->remaining_area                   = $calculateRemainingArea;

        $updatedFormData->total_grout_mat                  = $calculateGroutMaterial;
        $updatedFormData->total_remaining_mat              = $calculateRemainingMaterial;

        // =========================================================
        // 4) Agregar materiales a totalsDatas con measuring + units
        // =========================================================

        // ---- A) "grout_fill_material" (en realidad se comporta como REBAR en tus cálculos)
        // measuring = vertical_rebar_total (LF)
        // units     = vertical_total_rebar_tons (TONS)  <-- matemáticamente más correcto en reporte final
        $selectedMaterial = null;
        if (isset($updatedFormData->grout_fill_material)) {
            $selectedMaterial = json_decode($updatedFormData->grout_fill_material);
        }

        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material    = $selectedMaterial;

            // ✅ Base matemática (LF)
            $Agregarmaterial->measuring   = (float)$updatedFormData->vertical_grouted_area;
            $Agregarmaterial->op_unit   = 'sqft';

            // ✅ Units matemáticas (TONS)
            $Agregarmaterial->units       = (float)$updatedFormData->total_grout_mat;

            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }

        // ---- B) other_select_material (positioners/other)
        // measuring = vertical_postioner_other_total (base)
        // units     = measuring / unit_measure_value (si existe), si no = measuring
        $selectedMaterial = null;
        if (isset($updatedFormData->other_select_material)) {
            $selectedMaterial = json_decode($updatedFormData->other_select_material);
        }

        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material    = $selectedMaterial;

            $Agregarmaterial->measuring   = (float)$updatedFormData->vertical_postioner_other_total;
            $Agregarmaterial->op_unit   = 'sqft';

            $unit_measure_value = isset($selectedMaterial->unit_measure_value) ? (float)$selectedMaterial->unit_measure_value : 0;
            if ($unit_measure_value > 0) {
                $Agregarmaterial->units = round($Agregarmaterial->measuring / $unit_measure_value, 6);
            } else {
                $Agregarmaterial->units = (float)$Agregarmaterial->measuring;
            }

            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }

        // ---- C) vertical_fill_remaining (remaining material)
        // measuring = total_remaining_mat (base)
        // units     = measuring / unit_measure_value (si existe), si no = measuring
        $selectedMaterial = null;
        if (isset($updatedFormData->vertical_grout_material)) {
            $selectedMaterial = json_decode($updatedFormData->vertical_grout_material);
        }

        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material    = $selectedMaterial;

            $Agregarmaterial->measuring   = (float)$updatedFormData->vertical_grouted_area;
            $Agregarmaterial->op_unit   = 'sqft';
            $Agregarmaterial->units = (float)$updatedFormData->total_grout_mat;

            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }

        $selectedMaterial = null;
        if (isset($updatedFormData->vertical_fill_remaining)) {
            $selectedMaterial = json_decode($updatedFormData->vertical_fill_remaining);
        }

        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material    = $selectedMaterial;

            $Agregarmaterial->measuring   = (float)$updatedFormData->remaining_area;
            $Agregarmaterial->op_unit   = 'sqft';
            $Agregarmaterial->units = (float)$updatedFormData->total_remaining_mat;

            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }

        return $updatedFormData;
    }

    public function calculaBloque4(&$updatedFormData)
    {
        // echo "calculaBloque4<br>";
        $half_block_material = $updatedFormData->half_block_material;
        $selectedMaterial = json_decode($half_block_material);
        if ($selectedMaterial != null) {
            $materialLength = $selectedMaterial->length;
            $materialHeight = $selectedMaterial->height;

            $calculateUnit = $this->calculateHalfBlockUnit($materialHeight, $materialLength);
            $calculateSqUnit = $this->calculateHalfBlockSqUnit($calculateUnit);

            $calculateTotalCjSpace = $this->calculateTotalCjSpaces($updatedFormData);
            $calculateTotalCjMaterial = $this->calculateTotalCjMaterials($updatedFormData, $calculateTotalCjSpace);
            $calculateTotalCaulkingMaterial = $this->calculateTotalCaulkingMaterials($updatedFormData, $calculateTotalCjMaterial);
            $calculateTotalCjMaterial_ea = $this->calculateTotalCjMaterials_ea($updatedFormData, $calculateTotalCjSpace);
            $calculateTotalCaulkingMaterial_ea = $this->calculateTotalCaulkingMaterials_ea($updatedFormData, $calculateTotalCjMaterial);
            $calculateTotalHalfBlock = $this->calculateTotalHalfBlocks($updatedFormData, $calculateTotalCjMaterial, $materialLength);
            $calculateTotalHalfUnit = $this->calculateTotalHalfUnits($updatedFormData, $calculateTotalHalfBlock, $selectedMaterial->unit_measure_value);

            if (!is_nan($calculateUnit)) {
                $updatedFormData->half_block_lf_unit = $calculateUnit;
            }
            if (!is_nan($calculateSqUnit)) {
                $updatedFormData->half_block_sq_unit = $calculateSqUnit;
            }
            if (!is_nan($materialLength)) {
                $updatedFormData->half_block_length = $materialLength;
            }

            $updatedFormData->control_total_cj_spaces = $calculateTotalCjSpace;

            $updatedFormData->control_total_cj_material = $calculateTotalCjMaterial;
            $updatedFormData->control_total_cj_material_ea = $calculateTotalCjMaterial_ea;

            $selectedMaterial = json_decode($updatedFormData->control_material);
            if ($selectedMaterial != null) {
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $updatedFormData->control_total_cj_material;
                $Agregarmaterial->op_unit   = 'lf';
                $Agregarmaterial->units = $updatedFormData->control_total_cj_material_ea;

                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }


            $updatedFormData->control_total_caulking_material = $calculateTotalCaulkingMaterial;
            $updatedFormData->control_total_caulking_material_ea = $calculateTotalCaulkingMaterial_ea;
            $selectedMaterial = json_decode($updatedFormData->control_rod);
            if ($selectedMaterial != null) {
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $updatedFormData->control_total_caulking_material;
                $Agregarmaterial->op_unit   = 'lf';
                $Agregarmaterial->units = (float)$updatedFormData->control_total_caulking_material_ea;
                
                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }

            $updatedFormData->control_total_sq_ft = $calculateTotalHalfBlock;
            $selectedMaterial = json_decode($updatedFormData->half_block_material);
            $updatedFormData->total_half_unit = $calculateTotalHalfUnit;
            if ($selectedMaterial != null) {
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $updatedFormData->control_total_sq_ft;
                $Agregarmaterial->op_unit   = 'sqft';
                $Agregarmaterial->units = $updatedFormData->total_half_unit;
                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }
        }


        return $updatedFormData;
    }

    public function calculaBandMaterial(&$updatedFormData)
    {
        // echo "calculaBandMaterial<br>";
        return $updatedFormData;
    }

    public function calculaBloque2(&$updatedFormData)
    {
        // echo "calculaBloque2<br>";
        if (is_array($updatedFormData)) {
            $updatedFormData = (object)$updatedFormData;
        }

        // Si no viene coping_material, no hacemos nada
        if (!isset($updatedFormData->coping_material) || $updatedFormData->coping_material === null || $updatedFormData->coping_material === '') {
            return $updatedFormData;
        }

        // Puede venir como JSON string o como objeto
        $coping_material_raw = $updatedFormData->coping_material;

        $selectedMaterial = null;

        if (is_string($coping_material_raw)) {
            $selectedMaterial = json_decode($coping_material_raw);
        }

        if (is_object($coping_material_raw)) {
            $selectedMaterial = $coping_material_raw;
        }

        if ($selectedMaterial == null) {
            return $updatedFormData;
        }

        // Dimensiones con fallback a 0
        $material_length = isset($selectedMaterial->length) ? (float)$selectedMaterial->length : 0;
        $material_height = isset($selectedMaterial->height) ? (float)$selectedMaterial->height : 0;
        $material_width  = isset($selectedMaterial->width)  ? (float)$selectedMaterial->width  : 0;

        // Calcular unidad del coping (misma lógica)
        $calculate_unit = 0;
        if ($material_height > 0 && $material_length > 0) {
            $calculate_unit = $this->calculateCopingUnit($material_height, $material_length);
        }

        // Guardar en form data
        $updatedFormData->coping_material_height = $material_height;
        $updatedFormData->coping_material_width  = $material_width;
        $updatedFormData->coping_material_length = $material_length;
        $updatedFormData->coping_material_unit   = $calculate_unit;

        return $updatedFormData;
    }




    public function calculateWallUnit($height, $length)
    {
        // echo "calculateWallUnit<br>";
        try {
            return round(($height * $length) / 144, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateWallSqUnit($wallUnit)
    {
        // echo "calculateWallSqUnit<br>";
        try {
            return round(1 / $wallUnit, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateWallCubicUnit($length, $height, $width)
    {
        // echo "calculateWallCubicUnit<br>";
        try {
            $wallCubicArea = $length * $height * $width;
            return round(1 / ($wallCubicArea * 1728), 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }




    public function calculateCopingUnit($height, $length)
    {
        // echo "calculateCopingUnit<br>";
        try {
            return round(($height * $length) / 144, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateRebarUnit($height, $length)
    {
        // echo "calculateRebarUnit<br>";
        try {
            return round(($height * $length) / 144, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateRebarSqUnit($rebarUnit)
    {
        // echo "calculateRebarSqUnit<br>";
        try {
            return round(1 / $rebarUnit, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateHalfBlockUnit($height, $length)
    {
        // echo "calculateHalfBlockUnit<br>";
        try {
            return round(($height * $length) / 144, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateHalfBlockSqUnit($halfBlockUnit)
    {
        // echo "calculateHalfBlockSqUnit<br>";
        try {
            return round(1 / $halfBlockUnit, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateFoundationHeight($data)
    {
        // echo "calculateFoundationHeight<br>";
        try {
            return floatval(floatval($data->finish_floor) - floatval($data->top_of_footing));
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculatedWallHeight($data, $effectiveFoundation)
    {
        // echo "calculatedWallHeight<br>";
        try {
            return $effectiveFoundation + $data->wall_height;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationWallLength($data)
    {
        // echo "calculationWallLength<br>";
        //echo $data->wall_length;
        try {
            $riseDrop = ($data->rise_drop === "rise") ? $data->rise_value : $data->drop_value;
            return floatval($data->wall_length) + floatval($riseDrop);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationSquareArea($totalWallHeight, $totalWallLength)
    {
        // echo "calculationSquareArea<br>";
        try {
            return $totalWallHeight * $totalWallLength;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationCubicArea($data, $totalSqArea)
    {
        // echo "calculationCubicArea<br>";
        try {
            if (is_numeric($data->wall_structure_thickness) && (float)$data->wall_structure_thickness > 0) {
                return $data->wall_structure_thickness * $totalSqArea;
            }else{
                return $totalSqArea;
            }
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationCubicYards($data, $totalCubicArea)
    {
        // echo "calculationCubicYards<br>";
        try {
            return round($totalCubicArea / 27, 2);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationWallSquareUnit($data, $totalSqArea)
    {
        // echo "calculationWallSquareUnit<br>";
        try {
            return round($data->wall_material_square_unit * $totalSqArea, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationCopingTotal($data)
    {
        // echo "calculationCopingTotal<br>";
        try {
            return round(floatval($data->wall_length) * floatval($data->coping_material_quantity), 2);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationCopingTotalUnit($data, $total)
    {
        // echo "calculationCopingTotalUnit<br>";
        try {
            if ($data->coping_material_length > 0) {
                return round($total / ($data->coping_material_length / 12), 2);
            }
            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationTotalAnchors($data)
    {
        // echo "calculationTotalAnchors<br>";
        try {
            if ($data->anchor_total_spaces > 0 && $data->anchor_quantity > 0) {
                return round($data->anchor_total_spaces * $data->anchor_quantity, 2);
            }
            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationTotalAnchorCoping($data)
    {
        // echo "calculationTotalAnchorCoping<br>";
        try {
            if ($data->coping_material_length > 0)
                if ($data->top_wall_material != null) {

                    // Crear el objeto Material
                    //$selectedMaterial = new Material($data->top_wall_material);
                    $selectedMaterial = json_decode($data->top_wall_material);
                    return round($data->wall_length * ($data->coping_wall_side), 2);
                }


            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculationTotalAnchorCopingUnits($data, $total)
    {
        //echo $data;
        try {
            return round( $total / 20, 2);
            //return round( $total / 1, 2);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateTotalSpacesFilled($data)
    {
        // echo "calculateTotalSpacesFilled<br>";
        try {
            if ($data->rebar_spacing > 0 && $data->additional_spacing > 0) {
                return $data->wall_length / $data->rebar_spacing + $data->additional_spacing;
            }
            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateTotalLifts($data)
    {
        // echo "calculateTotalLifts<br>";
        try {
            if ($data->rebar_lift_spaces > 0)
                return round($data->total_wall_height / $data->rebar_lift_spaces, 3);

            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateRebarLfs($data, $totalLifts)
    {
        // echo "calculateRebarLfs<br>";
        try {
            return round(($data->rebar_lift_spaces + $data->vertical_rebar_overlap) * $totalLifts, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateVericalRebarTotals($data, $spacesFilled, $totalRebar)
    {
        // echo "calculateVericalRebarTotals<br>";
        try {
            return round($spacesFilled * $totalRebar * $data->bars_per_space, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateRebarTons($data, $totalRebarLfts)
    {

        try {
            return round($totalRebarLfts / $data->lft_rebar_per_ton, 3);
        } catch (\Throwable $th) {
            // echo "totalRebarLfts " . $totalRebarLfts . "<br>";
            // echo "data->lft_rebar_per_ton " . $data->lft_rebar_per_ton . "<br>";
            return 0;
        }
    }

    public function calculateRebarPerTons($data)
    {

        try {
            $selectedMaterial = json_decode($data);
            return round($selectedMaterial->shortton_wlf, 3);
        } catch (\Throwable $th) {
            // echo "totalRebarLfts " . $totalRebarLfts . "<br>";
            // echo "data->lft_rebar_per_ton " . $data->lft_rebar_per_ton . "<br>";
            return 0;
        }
    }

    public function calculatePostionPerTotals($data)
    {
        try {
            //code...
            return round($data->total_wall_height / $data->vertical_rebar_positioner, 3);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculatePostionOtherTotals($spacesFilled, $positionPerTotal)
    {
        // echo "calculatePostionOtherTotals<br>";
        try {
            return round($spacesFilled * $positionPerTotal, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateAreaGrouteds($data, $spacesFilled)
    {
        // echo "calculateAreaGrouteds<br>";
        try {
            return round($spacesFilled * $data->total_wall_height * 0.66, 3);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateRemainingAreas($data, $areaGrouted)
    {
        // echo "calculateRemainingAreas<br>";
        try {
            $total_sq_area_filled = 0;
            foreach ($data->courses as $course) {
                $course =  (object) $course;

                $total_sq_area_filled += $course->total_sq_area_filled;
            }

            return round($data->total_square_area - $total_sq_area_filled - $data->vertical_grouted_area, 2);
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function calculateGroutMaterials($data, $areaGrouted)
    {
        // echo "calculateGroutMaterials<br>";

        try {
            return round($areaGrouted / $data->sq_fill_mat_per_cy, 3);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateRemainingMaterials($data, $remainingArea)
    {
        // echo "calculateRemainingMaterials<br>";

        try {
            return round($remainingArea / $data->sq_fill_mat_per_cy, 3);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateFillMatPerCys($data)
    {
        // echo "calculateFillMatPerCys<br>";

        try {
            $selectedMaterial = json_decode($data->wall_material);
            return $selectedMaterial->sq_ft_per_cy;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalCjSpaces($data)
    {
        // echo "calculateTotalCjSpaces<br>";
        try {
            return round($data->wall_length / $data->control_spacing, 3);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalCjMaterials($data, $totalCjSpaces)
    {
        // echo "calculateTotalCjMaterials<br>";
        try {
            return round($data->total_wall_height * $totalCjSpaces, 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalCaulkingMaterials($data, $totalCjMaterials)
    {
        // echo "calculateTotalCaulkingMaterials<br>";
        try {
            return round($data->control_rod_side * $totalCjMaterials, 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalCjMaterials_ea($data, $totalCjSpaces)
    {
        // echo "calculateTotalCjMaterials_ea<br>";
        try {
            $selectedMaterial = json_decode($data->control_material);
            $control_material_length = $selectedMaterial->length / 12;
            return round($data->control_total_cj_material / $control_material_length, 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalCaulkingMaterials_ea($data, $totalCjMaterials)
    {
        // echo "calculateTotalCaulkingMaterials_ea<br>";
        try {
            $selectedMaterial = json_decode($data->control_rod);
            $control_material_length = $selectedMaterial->length / 12;
        
            return round(($data->control_total_cj_material * $data->control_rod_side) / $control_material_length, 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalHalfBlocks($data, $totalCjMaterials, $materialLength = null)
    {
        // echo "calculateTotalHalfBlocks<br>";
        
        try {
            $halfLength = isset($materialLength) ? $materialLength : $data->half_block_length;
            return round($totalCjMaterials * ($halfLength / 12), 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalHalfUnits($data, $halfBlock, $materialUnit = null)
    {
        // echo "calculateTotalHalfUnits<br>";
        
        try {
            $halfUnit = isset($materialUnit) ? $materialUnit : $data->half_block_lf_unit;

            return round($halfBlock * (1 / ($halfUnit)), 0);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }






    #region Courses

    public function handleUseCourse(&$formData)
    {
        // echo "handleUseCourse<br>";
        // Check if the course is already in the courses array
        try {
            $courses_new = [];
            $courses = $formData->courses;


            foreach ($courses as $selectedCourseData) {

                $selectedCourseData = (object)$selectedCourseData;
                // echo "selectedCourseData->name" . $selectedCourseData->name . "<br>";

                $nombre_course = '';
                if (isset($selectedCourseData->name) && trim((string)$selectedCourseData->name) !== '') {
                    $nombre_course = trim((string)$selectedCourseData->name);
                }

                if ($nombre_course === '' && isset($selectedCourseData->course_name) && trim((string)$selectedCourseData->course_name) !== '') {
                    $nombre_course = trim((string)$selectedCourseData->course_name);
                }

                if ($nombre_course === '') {
                    $nombre_course = 'Course';
                }



                //top_elevation
                $selectedCourseData->top_elevation =  round(
                    $selectedCourseData->band_height +
                        $selectedCourseData->bottom_elevation,
                    3
                );


                $selectedCourseData->material_sq_ft = floatval($formData->wall_length * $selectedCourseData->band_height);
                $selectedCourseData->total_material_units = $this->calculateTotalMaterialUnits($formData, $selectedCourseData);
                //Agregar material a la cuenta


                $band_material = null;

                if (isset($selectedCourseData->band_material) && $selectedCourseData->band_material != '') {
                    if (is_string($selectedCourseData->band_material)) {
                        $band_material = json_decode($selectedCourseData->band_material);
                    }

                    if (is_object($selectedCourseData->band_material)) {
                        $band_material = $selectedCourseData->band_material;
                    }

                    if (is_array($selectedCourseData->band_material)) {
                        $band_material = (object)$selectedCourseData->band_material;
                    }
                }

                $Agregarmaterial = new Total_Material();
                if (isset($band_material->id)) {
                    $Agregarmaterial->id_material = $band_material->id;
                    $Agregarmaterial->material = $band_material;
                    $Agregarmaterial->measuring = $selectedCourseData->material_sq_ft;
                    $Agregarmaterial->op_unit   = 'sqft';
                    $Agregarmaterial->units     = (float)$selectedCourseData->total_material_units;
                    $Agregarmaterial->principal = true;
                    $Agregarmaterial->source_type = 'course';
                    $Agregarmaterial->course_name = $nombre_course;
                    $Agregarmaterial->unitario_row_label = $nombre_course;
                    $Agregarmaterial->global_takeoff_suffix = $nombre_course;
                    $this->addtotalDatas($Agregarmaterial, $formData);

                    //total_courses
                    $band_material_height_feet = round(($band_material->height / 12), 6);
                    $total_courses = round(($selectedCourseData->band_height / $band_material_height_feet), 0);
                    $selectedCourseData->total_courses = $total_courses;
                }
                
                $rebar_material = null;

                if (isset($selectedCourseData->rebar_material) && $selectedCourseData->rebar_material != '') {
                    if (is_string($selectedCourseData->rebar_material)) {
                        $rebar_material = json_decode($selectedCourseData->rebar_material);
                    }

                    if (is_object($selectedCourseData->rebar_material)) {
                        $rebar_material = $selectedCourseData->rebar_material;
                    }

                    if (is_array($selectedCourseData->rebar_material)) {
                        $rebar_material = (object)$selectedCourseData->rebar_material;
                    }
                }

                $Agregarmaterial = new Total_Material();
                if (isset($rebar_material->id)) {

                    $selectedCourseData->total_per_each = round(
                        $selectedCourseData->rebar_overlap +
                            ($rebar_material->length / 12),
                        3
                    );

                    $selectedCourseData->total_rebar_length = $this->calcularTotalRebarLength($formData, $selectedCourseData, $rebar_material);
                    $selectedCourseData->total_rebar_lf = $this->calculateBandTotalRebarLfs($selectedCourseData, $this->calculateRebarCourses($formData, $selectedCourseData), $selectedCourseData->total_rebar_length);
                    $selectedCourseData->total_rebar_linear_feet = $this->calcularTotalRebarLinearFeet($selectedCourseData);
                    $selectedCourseData->total_rebar_units = $this->calcularTotalRebarUnits($selectedCourseData, $rebar_material);
                    $selectedCourseData->rebar_lf_ton = $this->calcularRebarLfTon($rebar_material);
                    $selectedCourseData->sq_ft_filled_grouted = $this->calcularSqFtFilledGrouted($selectedCourseData, $formData->wall_length);
                    $selectedCourseData->total_sq_area_filled = $this->calcularTotalSqAreaFilled($selectedCourseData);

                    $selectedCourseData->deducted_area_vertically = $this->calcularDeductedAreaVertically($selectedCourseData, $formData->total_spaces_filled);
                    $Agregarmaterial->id_material = $rebar_material->id;
                    $Agregarmaterial->material = $rebar_material;
                    $Agregarmaterial->measuring   = (float)$selectedCourseData->total_rebar_linear_feet;
                    $Agregarmaterial->op_unit   = 'lf';
                    $Agregarmaterial->units       = (float)$selectedCourseData->total_rebar_units;
                    $Agregarmaterial->principal = true;

                    $Agregarmaterial->source_type = 'course';
                    $Agregarmaterial->course_name = $nombre_course;
                    $Agregarmaterial->unitario_row_label = $nombre_course;
                    $Agregarmaterial->global_takeoff_suffix = $nombre_course;
                    $this->addtotalDatas($Agregarmaterial, $formData);

                }

                //Agregar material a la cuenta
                $fill_material = null;

                if (isset($selectedCourseData->fill_material) && $selectedCourseData->fill_material != '') {
                    if (is_string($selectedCourseData->fill_material)) {
                        $fill_material = json_decode($selectedCourseData->fill_material);
                    }

                    if (is_object($selectedCourseData->fill_material)) {
                        $fill_material = $selectedCourseData->fill_material;
                    }

                    if (is_array($selectedCourseData->fill_material)) {
                        $fill_material = (object)$selectedCourseData->fill_material;
                    }
                }
                $Agregarmaterial = new Total_Material();
                if (isset($fill_material->id)) {

                    $selectedMaterial = json_decode($formData->wall_material);
                    $selectedCourseData->sq_grouted_per_cy = $selectedMaterial->sq_ft_per_cy;
                    if (isset($selectedCourseData->sq_grouted_per_cy_manuality)) {
                        $selectedCourseData->sq_grouted_per_cy = ((float)$selectedCourseData->sq_grouted_per_cy_manuality > 0) ? (float)$selectedCourseData->sq_grouted_per_cy_manuality : $selectedCourseData->sq_grouted_per_cy;
                    }
                    $selectedCourseData->total_sq_fill_materials = $this->calcularTotalSqFillMaterials($selectedCourseData, $selectedCourseData->sq_grouted_per_cy);
                    $Agregarmaterial->id_material = $fill_material->id;
                    $Agregarmaterial->material = $fill_material;
                    $Agregarmaterial->measuring = $selectedCourseData->total_sq_fill_materials;
                    $Agregarmaterial->op_unit   = 'sqft';
                    $unit_measure_value = isset($fill_material->unit_measure_value) ? (float)$fill_material->unit_measure_value : 0;
                    if ($unit_measure_value > 0) {
                        $Agregarmaterial->units = round($Agregarmaterial->measuring / $unit_measure_value, 6);
                    } else {
                        $Agregarmaterial->units = (float)$Agregarmaterial->measuring;
                    }
                    $Agregarmaterial->principal = true;

                    $Agregarmaterial->source_type = 'course';
                    $Agregarmaterial->course_name = $nombre_course;
                    $Agregarmaterial->unitario_row_label = $nombre_course;
                    $Agregarmaterial->global_takeoff_suffix = $nombre_course;
                    $this->addtotalDatas($Agregarmaterial, $formData);
                }
                $selectedCourseData->area_grouted_sq = $this->calculateGroutedSqs($formData, $selectedCourseData);
                $selectedCourseData->total_grout_cy =  $this->calculateGroutedCys($selectedCourseData, $selectedCourseData->area_grouted_sq);
                $selectedCourseData->total_area_grout_sq = $this->calculateTotalGroutedCys($selectedCourseData, $selectedCourseData->total_grout_cy);







                $courses_new[] = $selectedCourseData;
            }
            $formData->courses = $courses_new;


            return $formData;
        } catch (\Throwable $th) {
            return $formData;
        }
    }
    public function calculateTotalMaterialUnits($data, $course)
    {
        // echo "calculateTotalMaterialUnits<br>";
        try {
            $wallLength = floatval($data->wall_length);
            $wallMaterialUnit = floatval($data->wall_material_unit);
            $bandHeight = floatval($course->band_height);
            $materialUnits = round(($wallLength * $bandHeight) / $wallMaterialUnit, 0);
            return $materialUnits;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalRebars($data, $course)
    {
        // echo "calculateTotalRebars<br>";
        try {
            $wallLength = floatval($data->wall_length);
            $wallMaterialLength = floatval($data->material_length);
            $totalPerEach = floatval($course->total_per_each);
            // $totalRebars = round($wallLength / ($wallMaterialLength * $totalPerEach), 3);
            $totalRebars = round(($wallLength / $wallMaterialLength) * $totalPerEach, 3);

        // echo "calculate Total Rebars<br>";
        // echo "wallLength: $wallLength<br>";
        // echo "wallMaterialLength: $wallMaterialLength<br>";
        // echo "totalPerEach: $totalPerEach<br>";

            return $totalRebars;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateRebarCourses($data, $course)
    {
        // echo "calculateRebarCourses<br>";
        try {
            $bandHeight = floatval($course->band_height);
            $wallMaterialHeight = floatval($data->material_height);
            $totalCourse = round($bandHeight / ($wallMaterialHeight / 12), 3);
            return $totalCourse;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateBandTotalRebarLfs($course, $rebarCourse, $totalRebarLength)
    {
        // echo "calculateBandTotalRebarLfs<br>";
        try {
            $rebarQuantity = floatval($course->rebar_quantity);
            $totalRebarLf = round(floatval($totalRebarLength) * $rebarQuantity * $rebarCourse, 3);
            return $totalRebarLf;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateGroutedSqs($data, $course)
    {
        // echo "calculateGroutedSqs<br>";
        try {
            $wallLength = floatval($data->wall_length);
            $bandHeight = floatval($course->band_height);
            $groutedSq = round($wallLength * $bandHeight, 3);
            return $groutedSq;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateGroutedCys($course, $groutedSq)
    {
        // echo "calculateGroutedCys<br>";
        // echo "course" . $course->sq_grouted_per_cy . "<br>";
        // echo "groutedSq" . $groutedSq . "<br>";
        try {
            $groutedPrCy = floatval($course->sq_grouted_per_cy);
            $groutedCy = round($groutedPrCy * $groutedSq, 3);
            return $groutedCy;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalGroutedCys($course, $groutedCy)
    {
        // echo "calculateTotalGroutedCys<br>";
        try {
            $totalGroutedCy = $groutedCy;
            return $totalGroutedCy;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }







    public function calcularTotalRebarLength($formData, $selectedCourseData, $rebar_material)
    {
        //   echo "calcularTotalRebarLength\n";
        try {
            $rebarLength = isset($rebar_material->length) ? (float)$rebar_material->length / 12 : 0;
            $rebarOverlap = isset($selectedCourseData->rebar_overlap) ? (float)$selectedCourseData->rebar_overlap : 0;

            $totalPerEach = number_format($rebarOverlap + $rebarLength, 3);

            $wallLength = isset($formData->wall_length) ? (float)$formData->wall_length : 0;

            $totalRebarLength = number_format(($wallLength / $rebarLength) * $totalPerEach, 3);

            //   echo "total_per_each: $totalPerEach\n";
            //   echo "wallLength: $wallLength\n";
            //   echo "rebarLength: $rebarLength\n";
            //   echo "totalRebarLength: $totalRebarLength\n";

            return $totalRebarLength;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calcularTotalRebarUnits($formData, $rebar_material)
    {
        //   echo "calcularTotalRebarUnits\n"; 
        try {
            $total_rebar_units = round(((float)$formData->total_rebar_linear_feet) /
                ($rebar_material->unit_measure_value), 2);

            return $total_rebar_units;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calcularTotalRebarLinearFeet($formData)
    {
        //   echo "calcularTotalRebarLinearFeet\n";
        try {
            $totalRebarLength = isset($formData->total_rebar_length) ? (float)$formData->total_rebar_length : 0;
            $rebarQuantity = isset($formData->rebar_quantity) ? (float)$formData->rebar_quantity : 0;
            $totalCourses = isset($formData->total_courses) ? (float)$formData->total_courses : 0;

            $totalLinearFeet = number_format($totalRebarLength * $rebarQuantity * $totalCourses, 0);

            //   echo "totalRebarLength: $totalRebarLength\n";
            //   echo "rebarQuantity: $rebarQuantity\n";
            //   echo "totalCourses: $totalCourses\n";
            //   echo "totalLinearFeet: $totalLinearFeet\n";

            return $totalLinearFeet;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calcularRebarLfTon($rebar_material)
    {
        //   echo "calcularRebarLfTon\n";
        try {
            $shortTonWlf = isset($rebar_material->shortton_wlf) ? (float)$rebar_material->shortton_wlf : 0;
            $totalTon = round($shortTonWlf, 2);


            return $totalTon;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calcularSqFtFilledGrouted($formData, $wallLength)
    {
        //   echo "calcularSqFtFilledGrouted\n";
        try {
            $bandHeight = isset($formData->band_height) ? (float)$formData->band_height : 0;


            $total = round($bandHeight * $wallLength, 3);

            //   echo "bandHeight: $bandHeight\n";
            //   echo "wallLength: $wallLength\n";
            //   echo "sqFtFilledGrouted: $total\n";

            return $total;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calcularDeductedAreaVertically($formData, $total_spaces_filled)
    {
        //   echo "calcularDeductedAreaVertically\n";
        try {
            $bandHeight = isset($formData->band_height) ? (float)$formData->band_height : 0;


            $total = round($bandHeight * $total_spaces_filled * 0.66, 3);

            //    echo "bandHeight: $bandHeight\n";
            //    echo "totalSpacesFilled: $total_spaces_filled \n";
            //    echo "deductedAreaVertically: $total\n";

            return $total;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calcularTotalSqAreaFilled($formData)
    {
        //   echo "calcularTotalSqAreaFilled\n";
        try {
            $sqFtFilledGrouted = isset($formData->sq_ft_filled_grouted) ? (float)$formData->sq_ft_filled_grouted : 0;
            $deductedAreaVertically = isset($formData->deducted_area_vertically) ? (float)$formData->deducted_area_vertically : 0;

            $total = round($sqFtFilledGrouted - $deductedAreaVertically, 2);

            //   echo "sqFtFilledGrouted: $sqFtFilledGrouted\n";
            //   echo "deductedAreaVertically: $deductedAreaVertically\n";
            //   echo "totalSqAreaFilled: $total\n";

            return $total;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calcularTotalSqFillMaterials($formData, $sqGroutedPerCy)
    {
        //   echo "calcularTotalSqFillMaterials\n";
        try {
            $totalSqAreaFilled = $formData->total_sq_area_filled;



            $total = ($sqGroutedPerCy > 0) ?  round($totalSqAreaFilled / $sqGroutedPerCy, 2) : round($totalSqAreaFilled / 90, 2);
            //   echo "totalSqAreaFilled: $totalSqAreaFilled\n";
            //   echo "sqGroutedPerCy: $sqGroutedPerCy\n";
            //   echo "totalSqFillMaterials: $total\n";

            return $total;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }









    #endregion region courses


    #region additional datas

    public function handleChangeAdditionalDatas(&$updatedFormData)
    {
        // echo "handleChange<br>";
        /*  $name = $event->name;
        $value = $event->value;
        preg_match('/\[(\d+)\]/', $name, $matches);
        $index = $matches ? (int) $matches[1] : 0;
        $indexedName = preg_replace('/\[\d+\]/', '', $name); */
        try {
            $additionalDatas = (object)$updatedFormData->additionalDatas;

            $additionalDatasFinal = [];

            foreach ($additionalDatas as $index  =>  $additionalData) {
                $total_measuring = 0;
                $total_units = 0;
                $op_unit = 0;
                $additionalData = (object)$additionalData;
                // $additionalDatas->additionalDatas[$index]=$additionalData;
                // echo "index " . $index . "<br>";
                // echo "additionalData " . json_encode($additionalData) . "<br>";



                $additional_material = $additionalData->additional_material;
                $selectedMaterial = json_decode($additional_material);
                if ($selectedMaterial != null) {


                    $materialLength = $selectedMaterial->length;
                    $materialHeight = $selectedMaterial->height;
                    $additionalData->additional_material_length = $materialLength;


                    $calculateLinealUnit = $this->calculateLinealUnits($index, $updatedFormData, $additionalData);
                    $calculateLinealTotalOverlap = $this->calculateLinealTotalOverlaps($index, $updatedFormData, $additionalData);
                    $calculateTotalLinealFt = $this->calculateTotalLinealFts($index, $updatedFormData, $calculateLinealUnit, $calculateLinealTotalOverlap);

                    $additionalData->lineal_units = $calculateLinealUnit;
                    $additionalData->lineal_total_overlap = $calculateLinealTotalOverlap;
                    $additionalData->lineal_total = $calculateTotalLinealFt;





                    $selectedMaterial = json_decode($additional_material);
                    $materialLength = $selectedMaterial->length;
                    $materialHeight = $selectedMaterial->height;
                    $additionalData->additional_material_length = $materialLength;


                    $calculateSpacingTotal = $this->calculateSpacingTotals($index, $updatedFormData, $additionalData);

                    $additionalData->spacing_total = $calculateSpacingTotal;





                    $calculateTotalUnitSqft = $this->calculateTotalUnitSqfts($additionalData, $updatedFormData->total_square_area);
                    $calculateTotalquantity = $this->calculateTotalquantity1($index, $updatedFormData);

                    $calculateTotalTopBottom = $this->calculateTotalTopBottom($index, $updatedFormData, $additionalData);
                    $calculatePerArea = $this->calculatePerArea($index, $updatedFormData, $additionalData);

                    $additionalData->total_unit_per_sq_ft = $calculateTotalUnitSqft;
                    $additionalData->total_unit_quantity = $calculateTotalquantity;
                    //$additionalDatas->top_bottom_total_units = $calculateTotalTopBottom;


                    //nuevos campos
                    $additionalData->lineal_total_spaces = 0;
                    if (isset($additionalData->lineal_spacing)) {

                        if ($additionalData->lineal_spacing > 0) {
                            $additionalData->lineal_total_spaces = $updatedFormData->total_wall_height / $additionalData->lineal_spacing;
                        }
                    }
                    if (!isset($additionalData->lineal_total_qty_space)) {
                        $additionalData->lineal_total_qty_space = 0;
                    }

                    $additionalData->lineal_total_ft = (
                        $additionalData->lineal_total_spaces *
                        $additionalData->lineal_total_overlap *
                        $additionalData->lineal_total_qty_space);
                    $additionalData->lineal_total_units = $this->calculateTotalLinealUnits($index, $updatedFormData, $calculateTotalLinealFt, $additionalData);

                    if (isset($additionalData->spacing_unit_overlap) && isset($additionalData->spacing_total_quantity_per_space)) {
                        $additionalData->spacing_total_overlap = $additionalData->spacing_total * $additionalData->spacing_unit_overlap;
                        $additionalData->spacing_total_ft = $additionalData->spacing_total_overlap * $additionalData->spacing_total_quantity_per_space;
                        $additionalData->spacing_total_units = round($additionalData->spacing_total_ft / ($materialLength / 12),2);

                        //$total_measuring = $additionalData->spacing_total_ft;
                        //$total_units = $additionalData->spacing_total_units;
                    }


                    $type = $additionalData->additional_type ?? '';

                    // ✅ 1) SPACING
                    if ($type === 'spacing') {
                        $total_measuring = (float)($additionalData->spacing_total_ft ?? 0);
                        $op_unit   = 'lf';
                        $total_units     = (float)($additionalData->spacing_total_units ?? 0);
                    }

                    // ✅ 2) QUANTITY
                    if ($type === 'quantity') {
                        $total_measuring = (float)($additionalData->total_unit_quantity ?? 0);
                        $op_unit   = '';
                        $total_units     = (float)($additionalData->total_unit_quantity ?? 0);
                    }

                    // ✅ 3) LINEAL
                    if ($type === 'lineal') {
                        $total_measuring = (float)($additionalData->lineal_total_ft ?? 0);
                        $op_unit   = 'lf';
                        $total_units     = (float)($additionalData->lineal_total_units ?? 0);
                    }

                    // ✅ 4) PER SQ FT
                    if ($type === 'pr_sq_ft') {
                        $total_measuring = (float)($additionalData->total_lineal_per_sq_ft ?? 0);  // base
                        $op_unit   = 'sqft';
                        $total_units     = (float)($additionalData->total_unit_per_sq_ft ?? 0);    // units
                    }

                    // ✅ 5) TOP & BOTTOM
                    if ($type === 'top_bottom') {
                        $total_measuring = (float)($additionalData->top_bottom_total_spaces ?? 0); // base (LF)
                        $op_unit   = 'lf';
                        $total_units     = (float)($additionalData->top_bottom_total_units ?? 0);  // units
                    }

                    // ✅ 6) PR AREA (sides)
                    if ($type === 'pr_area') {
                        $total_measuring = (float)($additionalData->total_lineal_pr_area ?? 0); // base
                        $op_unit   = 'sqft';
                        $total_units     = (float)($additionalData->total_unit_pr_area ?? 0);   // units
                    }
                    
                    //Agregar material a la cuenta
                    $Agregarmaterial = new Total_Material();
                    $Agregarmaterial->id_material = $selectedMaterial->id;
                    $Agregarmaterial->material = $selectedMaterial;
                    $Agregarmaterial->measuring = $total_measuring;
                    $Agregarmaterial->units = $total_units;
                    $Agregarmaterial->op_unit   = $op_unit;
                    $this->addtotalDatas($Agregarmaterial, $updatedFormData);
                }
                $additionalDatasFinal[] = $additionalData;
            }
            $updatedFormData->additionalDatas = $additionalDatasFinal;
            return $updatedFormData;
        } catch (\Throwable $th) {
            //throw $th;
            return $updatedFormData;
        }
    }


    public function handleChangeadjustmentDatas(&$updatedFormData)
    {
        // echo "handleChange<br>";
        /*  $name = $event->name;
        $value = $event->value;
        preg_match('/\[(\d+)\]/', $name, $matches);
        $index = $matches ? (int) $matches[1] : 0;
        $indexedName = preg_replace('/\[\d+\]/', '', $name); */
        try {
            $adjustmentDatas = [
                array(
                    "adjustment_material" => "0",

                    "adjustment_description" => "",

                    "adjustment_type" => "add",
                    "adjustment_qty" => "0",
                    "adjustment_unit" => "sqft",
                    "adjustment_measured_qty" => "0",

                )

            ];
            if (isset($updatedFormData->adjustmentDatas)) {
                $adjustmentDatas = (object)$updatedFormData->adjustmentDatas;
            }

            $adjustmentDatasFinal = [];

            foreach ($adjustmentDatas as $index  =>  $adjustmentData) {

                $adjustmentData = (object)$adjustmentData;
                // $adjustmentDatas->adjustmentDatas[$index]=$adjustmentData;
                // echo "index " . $index . "<br>";
                // echo "adjustmentData " . json_encode($adjustmentData) . "<br>";



                $adjustment_material = $adjustmentData->adjustment_material;
                $selectedMaterial = json_decode($adjustment_material);
                $adjustment_measured_qty = 0;
                if ($selectedMaterial != null) {

                    // $selectedMaterial = new Material($adjustment_material);
                    //$selectedMaterial = json_decode($adjustment_material);
                    if ($selectedMaterial->unit_measure_value > 0) {

                        $adjustment_measured_qty = $adjustmentData->adjustment_qty / $selectedMaterial->unit_measure_value;
                        $adjustmentData->unit_measure_value =  $selectedMaterial->unit_measure_value;
                    }

                    $adjustmentData->adjustment_measured_qty = $adjustment_measured_qty;


                    if ($adjustmentData->adjustment_type === "Deduct") {
                        $adjustmentData->adjustment_measured_qty = (-1) * $adjustment_measured_qty;
                    }

                    //Agregar material a la cuenta
                    $Agregarmaterial = new Total_Material();
                    $Agregarmaterial->id_material = $selectedMaterial->id;
                    $Agregarmaterial->material = $selectedMaterial;
                    $Agregarmaterial->measuring = $adjustmentData->adjustment_measured_qty;
                    $Agregarmaterial->op_unit   = 'sqft';
                    $Agregarmaterial->units       = (float)$adjustment_measured_qty;
                    $this->addtotalDatas($Agregarmaterial, $updatedFormData);
                }
                $adjustmentDatasFinal[] = $adjustmentData;
            }
            $updatedFormData->adjustmentDatas = $adjustmentDatasFinal;
            return $updatedFormData;
        } catch (\Throwable $th) {
            //throw $th;
            return $updatedFormData;
        }
    }

    public function calculateLinealUnits($index, $data, $additionalDatas)
    {
        // echo "calculateLinealUnits<br>";
        try {
            $materialLength = (float) $additionalDatas->additional_material_length;
            $linealQuantity = (float) $additionalDatas->lineal_quantity;
            $wallLength = (float) $data->wall_length;
            $total_units = round($wallLength / ($materialLength / 12), 2);
            return $total_units;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }


    public function calculateLinealTotalOverlaps($index, $data, $additionalDatas)
    {
        // echo "calculateLinealTotalOverlaps<br>";
        // $additionalDatas=(object)$data->additionalDatas[$index];
        try {
            $additional_material =  json_decode($additionalDatas->additional_material);

            $materialLength = (float) $data->total_wall_height;
            $linealOverlap = (float) $additionalDatas->lineal_unit_overlap;
            $totalOverlap = round($materialLength  + $linealOverlap, 2);
            return $totalOverlap;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalLinealFts($index, $data, $totalUnits, $totalOverlap)
    {
        // echo "calculateTotalLinealFts<br>";
        try {
            $additionalDatas = (object)$data->additionalDatas[$index];
            $linealQuantity = (float)  $additionalDatas->lineal_quantity;
            $totallinealFt = round($linealQuantity * $totalUnits * $totalOverlap, 2);
            return $totallinealFt;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalLinealUnits($index, $data, $totallineal, $additionalDatas)
    {
        // echo "calculateTotalLinealUnits<br>";
        try {
            $materialLength = (float)  $additionalDatas->additional_material_length;
            $totalLinealUnit = round($additionalDatas->lineal_total_ft / ($materialLength / 12), 2);
            return $totalLinealUnit;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateSpacingTotals($index, $data, $additionalDatas)
    {
        // echo "calculateSpacingTotals<br>";
        // $additionalDatas=(object)$data->additionalDatas[$index];
        try {
            $totalSpacing = 0;
            $wallLength = (float) $data->total_wall_length;
            $space = (float) $additionalDatas->spacing_space;
            if ($space > 0) {
                $totalSpacing = round($wallLength / $space, 2);
            }
            return $totalSpacing;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }



    public function calculateTotalUnitSqfts(&$additionalDatas, $total_square_area)
    {
        // echo "calculateTotalUnitSqfts<br>";
        try {
            $wallArea = (float) $total_square_area;
            $UnitSqft = (float) $additionalDatas->unit_per_sq_ft;
            $total_lineal_sqft = round($wallArea * $UnitSqft, 2);
            $materialLength = (float) $additionalDatas->additional_material_length;
            $materialLength_ft = $materialLength / 12;
            $additionalDatas->total_lineal_per_sq_ft = $total_lineal_sqft;
            $totalUnitSq = $total_lineal_sqft / $materialLength_ft;
            return round($totalUnitSq,2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalquantity1($index, $data)
    {
        // echo "calculateTotalquantity1<br>";
        try {
            $additionalDatas = (object)$data->additionalDatas[$index];
            $total_unit_quantity = (float) isset($additionalDatas->quantity) ? $additionalDatas->quantity : 0;
            return $total_unit_quantity;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalTopBottom($index, $updatedFormData, $additionalDatas)
    {
        // echo "calculateTotalTopBottom<br>";
        try {
            $top_bottom_spaces = (float) ($additionalDatas->top_bottom_spaces ?? 0);
            $top_bottom_total_spaces = $updatedFormData->wall_length * $top_bottom_spaces;
            $additionalDatas->top_bottom_total_spaces = $top_bottom_total_spaces;
            $top_bottom_total_units = round(($top_bottom_total_spaces /round(($additionalDatas->additional_material_length/12), 2)), 3);
            $additionalDatas->top_bottom_total_units = $top_bottom_total_units;
            return $top_bottom_total_units;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculatePerArea($index, $updatedFormData, $additionalDatas)
    {
        // echo "calculatePerArea<br>";
        try {
            $pr_area_sides = (float) ($additionalDatas->pr_area_sides ?? 0);
            $total_lineal_pr_area = $updatedFormData->total_square_area * $pr_area_sides;
            $additionalDatas->total_lineal_pr_area = $total_lineal_pr_area;
            $total_unit_pr_area = round(($total_lineal_pr_area /$updatedFormData->wall_material_unit), 3);
            $additionalDatas->total_unit_pr_area = $total_unit_pr_area;
            return $total_unit_pr_area;
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }


    #endregion additional datas



    #region total datas

    public function addtotalDatas($Total_Material, $updatedFormData)
    {
        //  $totalsDatasFinal = (array)$updatedFormData->totalsDatas;
        try {
            $this->totalsDatasFinal[] = $Total_Material;
            $updatedFormData->totalsDatas = $this->totalsDatasFinal;
            return $updatedFormData;
        } catch (\Throwable $th) {
            //throw $th;
            return $updatedFormData;
        }
    }

    public function Calcula_totalDatas(&$updatedFormData)
    {
        // echo "handleChange<br>";
        /*  $name = $event->name;
        $value = $event->value;
        preg_match('/\[(\d+)\]/', $name, $matches);
        $index = $matches ? (int) $matches[1] : 0;
        $indexedName = preg_replace('/\[\d+\]/', '', $name); */
        try {
            $totalsDatas = (object)$updatedFormData->totalsDatas;
        } catch (\Throwable $th) {
            //throw $th;
            return $updatedFormData;
        }



        foreach ($totalsDatas as $index  =>  $additionalData) {
            try {
                $additionalData = (object)$additionalData;
            } catch (\Throwable $th) {
                //throw $th;
                continue;
            }
            //dd($additionalData);
            // $additionalDatas->additionalDatas[$index]=$additionalData;
            // echo "index " . $index . "<br>";
            // echo "additionalData " . json_encode($additionalData) . "<br>";
            $totalsDatasFinal[] = $additionalData;
        }
        $updatedFormData->totalsDatas = $totalsDatasFinal;
        return $updatedFormData;
    }
    public function Calcula_totalDatas1(&$updatedFormData, $project, $crew, $trade, $takeoff_name)
    {
        $laborInfoArray = $crew[0]['labor_info'] ?? [];
        
        if (!is_array($laborInfoArray)) {
            $laborInfoArray = [];
        }

        $crew_cost_total = 0;
        $percentage_total = 0;
        $price_total = 0;
        $quantity = 0; // producción (cantidad crew)
        $hours_per_day = 0;

        foreach ($laborInfoArray as $labor_row) {
            $quantity      = isset($labor_row['quantity']) ? (float)$labor_row['quantity'] : 0;
            $hours_per_day = isset($labor_row['hours_per_day']) ? (float)$labor_row['hours_per_day'] : 0;

            $crew_cost_total  += isset($labor_row['crew_cost']) ? (float)$labor_row['crew_cost'] : 0;
            $percentage_total += isset($labor_row['percentage_total']) ? (float)$labor_row['percentage_total'] : 0;
            $price_total      += isset($labor_row['price_total']) ? (float)$labor_row['price_total'] : 0;
        }

        // Blindaje de proyecto
        if (is_object($project)) {
            $project = json_decode(json_encode($project), true);
        }
        if (!is_array($project)) {
            $project = [];
        }

        $project_tax     = isset($project['tax']) ? (float)$project['tax'] : 0;
        $project_oh      = isset($project['oh']) ? (float)$project['oh'] : 0;
        $project_profit  = isset($project['profit']) ? (float)$project['profit'] : 0;
        $project_weather = isset($project['weather']) ? (float)$project['weather'] : 0;

        $totalsDatasFinal = [];

        foreach ($updatedFormData as $index => $additionalData) {

            // Normalizar a array
            if (is_object($additionalData)) {
                $additionalData = json_decode(json_encode($additionalData), true);
            }
            if (!is_array($additionalData)) {
                $additionalData = [];
            }

            // Normalizar material (puede venir array, stdClass o JSON string)
            if (isset($additionalData['material'])) {
                if (is_object($additionalData['material'])) {
                    $additionalData['material'] = json_decode(json_encode($additionalData['material']), true);
                }

                if (is_string($additionalData['material'])) {
                    $decodedMaterial = json_decode($additionalData['material'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedMaterial)) {
                        $additionalData['material'] = $decodedMaterial;
                    }
                }
            }

            if (!isset($additionalData['material']) || !is_array($additionalData['material'])) {
                $additionalData['material'] = [];
            }

            // Blindaje campos base
            $additionalData['measuring'] = isset($additionalData['measuring']) ? (float)$additionalData['measuring'] : 0;

            // Defaults para evitar undefined index
            $additionalData['cost2'] = isset($additionalData['cost2']) ? (float)$additionalData['cost2'] : 0;

            $material_height = isset($additionalData['material']['height']) ? (float)$additionalData['material']['height'] : 0;
            $material_waste  = isset($additionalData['material']['waste']) ? (float)$additionalData['material']['waste'] : 0;
            $material_price  = isset($additionalData['material']['prices']) ? (float)$additionalData['material']['prices'] : 0;
            $production_rate = isset($additionalData['material']['production_rate']) ? (float)$additionalData['material']['production_rate'] : 0;

            $unit_measure_value = 0;
            if (isset($additionalData['material']['unit_measure_value'])) {
                $unit_measure_value = (float)$additionalData['material']['unit_measure_value'];
            }

            // =========================================================
            // 1) quantity (interno) basado en height (tu lógica original)
            // =========================================================
            $fraction_sq_ft = ($material_height > 0) ? ($material_height / 12) : 0.000001;

            $additionalData['quantity'] = round(
                ($fraction_sq_ft > 0) ? ($additionalData['measuring'] / $fraction_sq_ft) : 0,
                2
            );

            // =========================================================
            // 2) UNITS: RESPETAR SI YA VIENE, SI NO CALCULAR FALLBACK
            // =========================================================
            // Si ya viene "units" calculado desde los bloques, lo respetamos.
            $units_base = null;

            if (isset($additionalData['units']) && is_numeric($additionalData['units'])) {
                $units_base = (float)$additionalData['units'];
            }

            // Fallback si NO viene units
            if ($units_base === null) {
                if ($unit_measure_value > 0 && $additionalData['measuring'] > 0) {
                    // En tu sistema casi siempre: units = measuring / unit_measure_value
                    $units_base = $additionalData['measuring'] / $unit_measure_value;
                } else {
                    // Si no hay conversión, matemáticamente igualamos units a measuring
                    // (esto aplica a conteos / LF sin factor definido)
                    $units_base = $additionalData['measuring'];
                }
            }

            $additionalData['units'] = round($units_base, 6);

            // =========================================================
            // 3) Waste y totales
            // =========================================================
            $additionalData['waste'] = round(
                $additionalData['measuring'] * ($material_waste / 100),
                2
            );

            $additionalData['sq_ft'] = round(
                $additionalData['measuring'] + $additionalData['waste'],
                2
            );

            $additionalData['waste_units'] = round(
                $additionalData['units'] * ($material_waste / 100),
                6
            );

            $additionalData['units_total'] = round(
                ($additionalData['units'] > 0) ? ($additionalData['units'] + $additionalData['waste_units']) : 0,
                6
            );

            // Conversion “bonita” del reporte: units_total / sq_ft (si aplica)
            $additionalData['conversion'] = round(
                ($additionalData['sq_ft'] > 0) ? ($additionalData['units_total'] / $additionalData['sq_ft']) : 0,
                6
            );

            // =========================================================
            // 4) Costos
            // =========================================================
            $additionalData['cost_ea'] = round($material_price, 2);
            $additionalData['cost']    = round($additionalData['units_total'] * $additionalData['cost_ea'], 2);

            $additionalData['tax']   = round($additionalData['cost'] * ($project_tax / 100), 2);
            $additionalData['cost1'] = round($additionalData['cost'] + $additionalData['tax'], 2);

            // =========================================================
            // 5) Labor (days, labor cost)
            // =========================================================
           //dd([
            //    'units_total' => $additionalData['units_total'],
              //  'quantity' => $quantity,
                //'production_rate' => $production_rate
            //]);
            $additionalData['days'] = (
                $additionalData['units_total'] > 0 &&
                $quantity > 0 &&
                $production_rate > 0
            )
                ? (($additionalData['units_total'] / $quantity) / $production_rate)
                : 0;

            $additionalData['cost_day'] = $crew_cost_total * $additionalData['days'];
            $additionalData['burden']   = $percentage_total * $additionalData['days'];
            $additionalData['lab_cost'] = $additionalData['cost_day'] + $additionalData['burden'];

            // =========================================================
            // 6) Totales finales
            // =========================================================
            $additionalData['sub_total'] = $additionalData['cost'] + $additionalData['cost1'] + $additionalData['lab_cost'] + $additionalData['cost2'];

            $additionalData['oh']     = round($additionalData['sub_total'] * ($project_oh / 100), 2);
            $additionalData['profit'] = round($additionalData['sub_total'] * ($project_profit / 100), 2);
            $additionalData['weather'] = round($additionalData['sub_total'] * ($project_weather / 100), 2);

            $additionalData['total'] = $additionalData['sub_total'] + $additionalData['oh'] + $additionalData['profit'];

            // =========================================================
            // 7) Extras para reporte global (sin pisar conversion)
            // =========================================================
            $takeoff_name_final = $takeoff_name;

            if (isset($additionalData['unitario_row_label']) && trim((string)$additionalData['unitario_row_label']) !== '') {
                $takeoff_name_final = trim($takeoff_name . ' - ' . $additionalData['unitario_row_label']);
            }

            $additionalData['takeoff_name'] = $takeoff_name_final;
            $additionalData['trade_name']       = $trade;
            $additionalData['project']          = $project;

            // Guardamos el fraction para debug si lo ocupas, pero NO lo metemos en "conversion"
            $additionalData['fraction_sq_ft']   = $fraction_sq_ft;

            $additionalData['production']       = $quantity;
            $additionalData['crew_cost_total']  = $crew_cost_total;
            $additionalData['hours_per_day']    = $hours_per_day;
            $additionalData['price_total']      = $price_total;

            $totalsDatasFinal[] = $additionalData;
        }

        $updatedFormData = $totalsDatasFinal;
        return $updatedFormData;
    }

    public function addCrew($project_id)
    {
        $projectId = $project_id;
        $laborClassId = 4;

        // 1) Traer crews del proyecto
        $crews = DB::table('crews')
            ->where('project_id', $projectId)
            ->select(['id', 'name', 'labor_info'])
            ->get();

        // 2) Recolectar labor_type_id desde todos los labor_info
        $laborIds = [];

        foreach ($crews as $crew) {
            $laborInfoArray = json_decode($crew->labor_info ?? '[]', true);

            if (!is_array($laborInfoArray)) {
                $laborInfoArray = [];
            }

            foreach ($laborInfoArray as $item) {
                $laborTypeId = isset($item['labor_type_id']) ? (int)$item['labor_type_id'] : 0;

                if ($laborTypeId > 0) {
                    $laborIds[] = $laborTypeId;
                }
            }
        }

        $laborIds = array_values(array_unique($laborIds));

        // 3) Consultar labors (una sola vez) + filtro labor_class_id = 4
        $laborsById = collect();

        if (!empty($laborIds)) {
            $labors = DB::table('labors')
                ->where('labor_class_id', $laborClassId)
                ->whereIn('id', $laborIds)
                ->select(['id', 'cost_per_hour', 'burdens', 'total_cost'])
                ->get();

            $laborsById = $labors->keyBy('id');
        }

        // 4) Enriquecer labor_info por cada crew
        $crewsEnriquecidas = [];

        foreach ($crews as $crew) {
            $laborInfoArray = json_decode($crew->labor_info ?? '[]', true);

            if (!is_array($laborInfoArray)) {
                $laborInfoArray = [];
            }

            $laborInfoEnriquecido = [];

            foreach ($laborInfoArray as $item) {
                $laborTypeId = isset($item['labor_type_id']) ? (int)$item['labor_type_id'] : 0;
                $labor = $laborsById->get($laborTypeId);

                $costPerHour  = $labor ? (float)$labor->cost_per_hour : 0;
                $hoursPerDay  = isset($item['hours_per_day']) ? (float)$item['hours_per_day'] : 0;
                $quantity     = isset($item['measuring']) ? (float)$item['measuring'] : 0;

                $payPerDayPerPerson = $costPerHour * $hoursPerDay;

                // Costo del crew para ese labor_type (por dÃ­a)
                $crewCost = $payPerDayPerPerson * $quantity;

                // Agrega datos del labor (si existe)
                $burdensArray = [];

                if ($labor && !empty($labor->burdens)) {
                    $decoded = json_decode($labor->burdens, true);
                    $burdensArray = is_array($decoded) ? $decoded : [];
                }

                // Totales de burdens para este crewCost
                $percentageTotal = 0;
                $priceTotal = 0;

                foreach ($burdensArray as $b) {
                    $percentage = isset($b['percentage']) ? (float)$b['percentage'] : 0;
                    $price      = isset($b['price']) ? (float)$b['price'] : 0;

                    if ($percentage > 0) {
                        $percentageTotal += $crewCost * ($percentage / 100);
                    }

                    if ($price > 0) {
                        // Caso A: price es fijo por crew (por dÃ­a)
                        $priceTotal += $price;

                        // Caso B (si price fuera por persona), usa esto en vez de lo de arriba:
                        // $priceTotal += $price * $quantity;
                    }
                }

                $item['cost_per_hour'] = $labor ? (float)$labor->cost_per_hour : 0;
                $item['burdens']       = $burdensArray; // <-- ahora es array
                $item['total_cost']    = $labor ? (float)$labor->total_cost : 0;
                $item['pay_per_day']   = round($payPerDayPerPerson, 2);
                $item['crew_cost']     = round($crewCost, 2);
                $item['percentage_total']   = round($percentageTotal, 2);
                $item['price_total']        = round($priceTotal, 2);


                $laborInfoEnriquecido[] = $item;
            }

            $crewsEnriquecidas[] = [
                'crew_id'    => $crew->id,
                'crew_name'  => $crew->name,
                'labor_info' => $laborInfoEnriquecido,
            ];
        }

        return $crewsEnriquecidas;
    }
    /*function agruparMaterialesPorId($materiales)
    {
        $resultadosAgrupados = [];

        foreach ($materiales as $material) {
            // Aseguramos que el material tiene un id_material vÃ¡lido
            $material = (object)$material;
            if (isset($material->id_material)) {
                $id = $material->id_material;

                // Si no existe el material en el arreglo agrupado, inicializarlo
                if (!isset($resultadosAgrupados[$id])) {
                    $resultadosAgrupados[$id] = [
                        'material' => $material->material,
                        'total' => 0,
                        'measuring' => 0,
                        'op_unit' => '',
                        'units' => 0,
                        'quantity' => 0,

                        'waste'      => 0,
                        'sq_ft'      => 0,
                        'units_total'      => 0,
                        'cost_ea'    => 0,
                        'cost'       => 0,
                        'tax'        => 0,
                        'cost1'      => 0,
                        'cost_day'   => 0,
                        'burden'     => 0,
                        'lab_cost'   => 0,
                        'days'       => 0,
                        'cost2'      => 0,
                        'sub_total'  => 0,
                        'oh'         => 0,
                        'profit'     => 0,
                        'weather'     => 0,
                        'total'      => 0,
                    ];
                }

                // Sumar los valores de total, measuring y quantity
                $resultadosAgrupados[$id]['total'] += isset($material->total) ? $material->total : 0;
                $resultadosAgrupados[$id]['measuring'] += isset($material->measuring) ? $material->measuring : 0;
                $resultadosAgrupados[$id]['op_unit'] = isset($material->op_unit) ? $material->op_unit : '';
                //$resultadosAgrupados[$id]['quantity'] += isset($material->quantity) ? $material->quantity : 0;

                $resultadosAgrupados[$id]['waste']     += (float) ($material->waste ?? 0);
                $resultadosAgrupados[$id]['sq_ft']     += (float) ($material->sq_ft ?? 0);
                $resultadosAgrupados[$id]['units']     += (float) ($material->units ?? 0);
                $resultadosAgrupados[$id]['units_total']     += (float) ($material->units_total ?? 0);

                $resultadosAgrupados[$id]['cost_ea']   += (float) ($material->cost_ea ?? 0);
                $resultadosAgrupados[$id]['cost']      += (float) ($material->cost ?? 0);
                $resultadosAgrupados[$id]['tax']       += (float) ($material->tax ?? 0);
                $resultadosAgrupados[$id]['cost1']     += (float) ($material->cost1 ?? 0);
                $resultadosAgrupados[$id]['cost_day']  += (float) ($material->cost_day ?? 0);
                $resultadosAgrupados[$id]['burden']    += (float) ($material->burden ?? 0);
                $resultadosAgrupados[$id]['lab_cost']  += (float) ($material->lab_cost ?? 0);
                $resultadosAgrupados[$id]['days']      += (float) ($material->days ?? 0);
                $resultadosAgrupados[$id]['cost2']     += (float) ($material->cost2 ?? 0);
                $resultadosAgrupados[$id]['sub_total'] += (float) ($material->sub_total ?? 0);
                $resultadosAgrupados[$id]['oh']        += (float) ($material->oh ?? 0);
                $resultadosAgrupados[$id]['profit']    += (float) ($material->profit ?? 0);
                $resultadosAgrupados[$id]['weather']    += (float) ($material->weather ?? 0);
                $resultadosAgrupados[$id]['total']     += (float) ($material->total ?? 0);
            }
        }

        return $resultadosAgrupados;
    }
    /*function generarTablaHtml($materialesAgrupados)
    {
        // Helpers locales
        $num = function ($v) {
            return is_numeric($v) ? (float)$v : 0.0;
        };

        // Columnas a sumar (todas las numÃ©ricas que imprimes)
        $sumCols = [
            'measuring', 'quantity', 'waste', 'sq_ft', 'units',
            'cost_ea', 'cost', 'tax', 'cost1',
            'cost_day', 'burden', 'lab_cost', 'days',
            'cost2', 'sub_total', 'oh', 'profit', 'weather', 'total'
        ];

        // Inicializa totales
        $totals = array_fill_keys($sumCols, 0.0);

        // Estilos (sin depender de tu layout; si ya tienes Bootstrap, se verÃ¡ mejor)
        $html = '
        <style>
            .xeon-table-wrap { width: 100%; overflow: auto; border: 1px solid #e9ecef; border-radius: 10px; }
            .xeon-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1400px; }
            .xeon-table thead th {
                position: sticky; top: 0; z-index: 2;
                background: #f8f9fa; border-bottom: 1px solid #e9ecef;
                padding: 10px 12px; font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: .02em;
                white-space: nowrap;
            }
            .xeon-table tbody td, .xeon-table tfoot td {
                padding: 10px 12px; border-bottom: 1px solid #f1f3f5; vertical-align: middle;
                background: #fff;
            }
            .xeon-table tbody tr:nth-child(odd) td { background: #fcfcfd; }
            .xeon-table td.text-end, .xeon-table th.text-end { text-align: right; }
            .xeon-table td.text-center, .xeon-table th.text-center { text-align: center; }
            .xeon-table .mono { font-variant-numeric: tabular-nums; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
            .xeon-table .material-cell { max-width: 320px; }
            .xeon-table .material-name { font-weight: 700; display: block; }
            .xeon-table .material-id { color: #6c757d; font-size: 12px; display: block; margin-top: 2px; }
            .xeon-table tfoot td {
                position: sticky; bottom: 0; z-index: 1;
                background: #eef2ff;
                border-top: 2px solid #dbe4ff;
                font-weight: 800;
                white-space: nowrap;
            }
            .xeon-badge {
                display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px;
                background:#e7f5ff; color:#1c7ed6; border:1px solid #d0ebff;
            }
            .xeon-table th.col-id,
            .xeon-table td.col-id{
            width: 60px;
            max-width: 60px;
            min-width: 60px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 8px;
            white-space: nowrap;
            }

            /* AÃºn mÃ¡s chico en pantallas grandes * /
            @media (min-width: 992px){
            .xeon-table th.col-id,
            .xeon-table td.col-id{
                width: 50px;
                max-width: 50px;
                min-width: 50px;
                font-size: 11px;
            }
            }
            @media print {
                .xeon-table thead th { position: static !important; }
                .xeon-table tfoot td { position: static !important; }
            }
        </style>';

        $html .= '<div class="xeon-table-wrap">';
        $html .= '<table class="xeon-table">';
        $html .= '<thead>
            <tr>
                <th class="col-id">ID Material</th>
                <th>Material</th>
                <th class="text-end">Measuring</th>
                <th class="text-end">Units</th>
                <th class="text-end">Quantity</th>
                <th class="text-end">Waste</th>
                <th class="text-end">SQ FT</th>
                <th class="text-end">Units total</th>
                <th class="text-end">Cost ea</th>
                <th class="text-end">Cost</th>
                <th class="text-end">Tax</th>
                <th class="text-end">Cost</th>
                <th class="text-end">Cost/day</th>
                <th class="text-end">Burden</th>
                <th class="text-end">Lab Cost</th>
                <th class="text-end">Days</th>
                <th class="text-end">Cost</th>
                <th class="text-end">Sub Total</th>
                <th class="text-end">OH</th>
                <th class="text-end">Profit</th>
                <th class="text-end">Weather</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($materialesAgrupados as $idMaterial => $datos) {

            $material = (object)($datos['material'] ?? []);
            $measurementUnit = $material->measurement_unit ?? '';

            // Sumar totales por columna
            foreach ($sumCols as $col) {
                $totals[$col] += $num($datos[$col] ?? 0);
            }

            $html .= '<tr>';
            $html .= '<td class="col-id">' . htmlspecialchars((string)$idMaterial) . '</td>';

            $matName = trim(($material->name ?? '') . ' ' . ($material->unique_id ?? ''));
            $html .= '<td class="material-cell">
                        <span class="material-name">' . htmlspecialchars($matName) . '</span>
                    </td>';

            $html .= '<td class="text-end mono">' . number_format($num($datos['measuring'] ?? 0), 2) . ' '. htmlspecialchars((string)$measurementUnit) .'</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['units'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['quantity'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['waste'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['sq_ft'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['units_total'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost_ea'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['tax'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost1'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost_day'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['burden'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['lab_cost'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['days'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost2'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['sub_total'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['oh'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['profit'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['weather'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['total'] ?? 0), 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';

        // Fila FINAL de totales
        $html .= '<tfoot><tr>';
        $html .= '<td class="text-center mono" colspan="2">TOTAL</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['measuring'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['units'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['quantity'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['waste'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['sq_ft'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['units_total'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost_ea'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['tax'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost1'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost_day'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['burden'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['lab_cost'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['days'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost2'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['sub_total'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['oh'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['profit'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['weather'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['total'], 2) . '</td>';
        $html .= '</tr></tfoot>';

        $html .= '</table></div>';

        return $html;
    }*/

    function agruparMaterialesPorId($materiales)
    {
        $resultadosAgrupados = [];

        foreach ($materiales as $material) {
            $material = (object)$material;

            if (!isset($material->id_material)) {
                continue;
            }

            $id_material_real = (string)$material->id_material;
            $etiqueta_unitaria = '';

            if (isset($material->unitario_row_label) && trim((string)$material->unitario_row_label) !== '') {
                $etiqueta_unitaria = trim((string)$material->unitario_row_label);
            }  

            $clave_agrupacion = $id_material_real;

            if ($etiqueta_unitaria !== '') {
                $clave_agrupacion = $id_material_real . '|' . md5($etiqueta_unitaria);
            }

            if (!isset($resultadosAgrupados[$clave_agrupacion])) {
                $resultadosAgrupados[$clave_agrupacion] = [
                    'display_id' => $id_material_real,
                    'unitario_row_label' => $etiqueta_unitaria,
                    'material' => $material->material,
                    'measuring' => 0,
                    'op_unit' => '',
                    'units' => 0,
                    'quantity' => 0,
                    'waste' => 0,
                    'sq_ft' => 0,
                    'units_total' => 0,
                    'cost_ea' => 0,
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
                ];
            }

            $resultadosAgrupados[$clave_agrupacion]['measuring'] += isset($material->measuring) ? (float)$material->measuring : 0;
            $resultadosAgrupados[$clave_agrupacion]['op_unit'] = isset($material->op_unit) ? $material->op_unit : '';

            $resultadosAgrupados[$clave_agrupacion]['waste'] += (float)($material->waste ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['sq_ft'] += (float)($material->sq_ft ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['units'] += (float)($material->units ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['units_total'] += (float)($material->units_total ?? 0);

            $resultadosAgrupados[$clave_agrupacion]['cost_ea'] += (float)($material->cost_ea ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['cost'] += (float)($material->cost ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['tax'] += (float)($material->tax ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['cost1'] += (float)($material->cost1 ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['cost_day'] += (float)($material->cost_day ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['burden'] += (float)($material->burden ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['lab_cost'] += (float)($material->lab_cost ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['days'] += (float)($material->days ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['cost2'] += (float)($material->cost2 ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['sub_total'] += (float)($material->sub_total ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['oh'] += (float)($material->oh ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['profit'] += (float)($material->profit ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['weather'] += (float)($material->weather ?? 0);
            $resultadosAgrupados[$clave_agrupacion]['total'] += (float)($material->total ?? 0);
        }

        uasort($resultadosAgrupados, function ($material_izquierdo, $material_derecho) {
            $id_izquierdo = isset($material_izquierdo['display_id']) ? (int)$material_izquierdo['display_id'] : 0;
            $id_derecho = isset($material_derecho['display_id']) ? (int)$material_derecho['display_id'] : 0;

            if ($id_izquierdo === $id_derecho) {
                $etiqueta_izquierda = isset($material_izquierdo['unitario_row_label']) ? (string)$material_izquierdo['unitario_row_label'] : '';
                $etiqueta_derecha = isset($material_derecho['unitario_row_label']) ? (string)$material_derecho['unitario_row_label'] : '';

                return strcasecmp($etiqueta_izquierda, $etiqueta_derecha);
            }

            return $id_izquierdo <=> $id_derecho;
        });

        return $resultadosAgrupados;
    }

    function generarTablaHtml($materialesAgrupados, array $meta = [])
    {
        // Helpers locales
        $num = function ($v) {
            return is_numeric($v) ? (float)$v : 0.0;
        };

        // ===========================
        // META del reporte (para mÃºltiples reportes en una hoja)
        // ===========================
        $report_id   = (string)($meta['report_id'] ?? ('rpt_' . substr(sha1(json_encode($meta) . microtime(true)), 0, 10)));
        $report_type = (string)($meta['report_type'] ?? 'REPORT'); // LENGTH | AREA | PERIMETER
        $trade_name  = (string)($meta['trade_name'] ?? '');
        $trade  = (string)($meta['trade'] ?? '');
        $scope_label = (string)($meta['scope_label'] ?? '');       // ej: "takeoff 11" / "Wall A"
        $mat_label   = (string)($meta['material_label'] ?? '');    // ej: "CMU5178 â€¢ 8\" light weight"
        $generated   = (string)($meta['generated_at'] ?? date('Y-m-d H:i'));

        $safe_report_id = htmlspecialchars($report_id, ENT_QUOTES, 'UTF-8');

        // Columnas a sumar (todas las numÃ©ricas que imprimes)
        $sumCols = [
            'measuring', 'units', 'waste', 'sq_ft', 'units_total',
            'cost_ea', 'cost', 'tax', 'cost1',
            'cost_day', 'burden', 'lab_cost', 'days',
            'cost2', 'sub_total', 'oh', 'profit', 'weather', 'total'
        ];

        // Inicializa totales
        $totals = array_fill_keys($sumCols, 0.0);

        // ===========================
        // CSS (una vez por reporte)
        // ===========================
        $html = '
            <style>
                /* ===== Card look (igualito al popup/por-material) ===== */
                .xeon-report{
                    border:1px solid #e5e7eb;
                    border-radius:10px;
                    overflow:hidden;
                    margin:14px 0;
                    font-family: Arial, sans-serif;
                    background:#fff;
                    box-shadow:0 6px 18px rgba(0,0,0,.06);
                }

                .xeon-report__header{
                    padding:10px 12px;
                    background:#f8fafc;
                    border-bottom:1px solid #e5e7eb;
                    display:flex;
                    gap:10px;
                    flex-wrap:wrap;
                    align-items:center;
                    justify-content:space-between;
                }

                .xeon-report__title{
                    display:flex;
                    flex-direction:column;
                    gap:4px;
                    min-width:260px;
                }

                .xeon-report__badge{
                    display:inline-block;
                    align-self:flex-start;
                    padding:4px 10px;
                    border:1px solid #cbd5e1;
                    border-radius:999px;
                    font-size:12px;
                    font-weight:700;
                    letter-spacing:.3px;
                    text-transform:uppercase;
                    background:#fff;
                    color:#111;
                }

                .xeon-report__headline{
                    font-weight:800;
                    font-size:13px;
                    text-transform:uppercase;
                    color:#111;
                }

                .xeon-report__subline{
                    color:#6c757d;
                    font-size:12px;
                }

                .xeon-report__meta{
                    display:grid;
                    gap:4px;
                    min-width:320px;
                }
                .xeon-report__meta .k{
                    display:inline-block;
                    width:98px;
                    color:#6c757d;
                    font-size:11px;
                    text-transform:uppercase;
                    letter-spacing:.04em;
                }
                .xeon-report__meta .v{
                    font-size:12px;
                    font-weight:700;
                    color:#111;
                }

                .xeon-report__divider{
                    height:1px;
                    background:#e5e7eb;
                }

                /* ===== Table (igualito al por-material pero conservando sticky) ===== */
                .xeon-table-wrap{
                    width:100%;
                    overflow:auto;
                    border-top:1px solid #edf2f7;
                }

                .xeon-table{
                    width:100%;
                    border-collapse:collapse; /* clave para look â€œpopupâ€ */
                    min-width:1400px;
                }

                .xeon-table th,
                .xeon-table td{
                    border-bottom:1px solid #eef2f7;
                    padding:8px 10px;
                    font-size:12px;
                    vertical-align:middle;
                    background:#fff;
                    white-space:nowrap;
                }

                .xeon-table thead th{
                    position:sticky;
                    top:0;
                    z-index:2;
                    background:#fff;
                    border-bottom:1px solid #e5e7eb;
                    text-transform:uppercase;
                    font-size:11px;
                    letter-spacing:.3px;
                    font-weight:800;
                }

                .xeon-table tbody tr:nth-child(odd) td{
                    background:#fcfcfd;
                }

                .xeon-table td.text-end,
                .xeon-table th.text-end{ text-align:right; }

                .xeon-table td.text-center,
                .xeon-table th.text-center{ text-align:center; }

                .xeon-table .mono{
                    font-variant-numeric: tabular-nums;
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                }

                .xeon-table .material-cell{
                    width: 360px;
                    max-width: 360px;
                    min-width: 360px;
                    overflow: hidden;
                    vertical-align: middle;
                }
                
                .xeon-table .material-name-ellipsis{
                    display: block;
                    width: 100%;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    font-weight: 900;
                }

                .xeon-table .material-name{ font-weight:900; display:block; }
                .xeon-table .material-id{ color:#6c757d; font-size:12px; display:block; margin-top:2px; }

                /* Totales: estilo por-material */
                .xeon-table tfoot td{
                    position:sticky;
                    bottom:0;
                    z-index:1;
                    font-weight:800;
                    background:#fffdf2;
                    border-top:2px solid #111827;
                }

                /* ID column */
                .xeon-table th.col-id,
                .xeon-table td.col-id{
                    width:60px; max-width:60px; min-width:60px;
                    text-align:center;
                    font-size:12px;
                    font-weight:900;
                    padding:6px 8px;
                    background:inherit;
                }
                @media (min-width: 992px){
                    .xeon-table th.col-id,
                    .xeon-table td.col-id{
                        width:50px; max-width:50px; min-width:50px;
                        font-size:11px;
                    }
                }
            </style>';

        // ===========================
        // WRAPPER del reporte (para mÃºltiples reportes)
        // ===========================
        $html .= '<section class="xeon-report" id="report_' . $safe_report_id . '" data-report-id="' . $safe_report_id . '">';
        $html .= '  <div class="xeon-report__header">';
        $html .= '    <div class="xeon-report__title">';
        $html .= '      <span class="xeon-report__badge">' . htmlspecialchars($report_type, ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '      <div class="xeon-report__headline">Trade / ' . htmlspecialchars($trade ?: ' Report', ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '      <div class="xeon-report__subline">' . htmlspecialchars($scope_label, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '    </div>';

        $html .= '    <div class="xeon-report__meta">';
        $html .= '      <div><span class="k">Report ID</span><span class="v">' . htmlspecialchars($report_id, ENT_QUOTES, 'UTF-8') . '</span></div>';
        $html .= '      <div><span class="k">Material</span><span class="v">' . htmlspecialchars($mat_label, ENT_QUOTES, 'UTF-8') . '</span></div>';
        $html .= '      <div><span class="k">Generated</span><span class="v">' . htmlspecialchars($generated, ENT_QUOTES, 'UTF-8') . '</span></div>';
        $html .= '    </div>';
        $html .= '  </div>';
        $html .= '  <div class="xeon-report__divider"></div>';

        // ===========================
        // TABLA
        // ===========================
        $html .= '<div class="xeon-table-wrap">';
        $html .= '<table class="xeon-table">';

        // Nota: renombro headers repetidos "Cost" para que se identifiquen mejor
        $html .= '<thead>
            <tr>
                <th class="col-id">ID</th>
                <th>Material</th>
                <th class="text-end">Measuring Area</th>
                <th class="text-end">Units</th>
                <th class="text-end">Waste</th>
                <th class="text-end">SQ FT</th>
                <th class="text-end">Units total</th>
                <th class="text-end">Cost ea</th>
                <th class="text-end">Cost</th>
                <th class="text-end">Tax</th>
                <th class="text-end">Cost + Tax</th>
                <th class="text-end">Cost/day</th>
                <th class="text-end">Burden</th>
                <th class="text-end">Lab Cost</th>
                <th class="text-end">Days</th>
                <th class="text-end">Cost (2)</th>
                <th class="text-end">Sub Total</th>
                <th class="text-end">OH</th>
                <th class="text-end">Profit</th>
                <th class="text-end">Weather</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>';
        foreach ($materialesAgrupados as $idMaterial => $datos) {

            $material = (object)($datos['material'] ?? []);
            $measurementUnit = $material->measurement_unit ?? '';
            $defaultUnit = $material->default_unit ?? '';
            $defaultUnitmeasure = $datos['op_unit'] != '' ? $datos['op_unit'] : 'lf';

            // Sumar totales por columna
            foreach ($sumCols as $col) {
                $totals[$col] += $num($datos[$col] ?? 0);
            }
            
            $html .= '<tr>';
            $display_id = isset($datos['display_id']) ? $datos['display_id'] : $idMaterial;
            $html .= '<td class="col-id">' . htmlspecialchars((string)$display_id, ENT_QUOTES, 'UTF-8') . '</td>';

            $matName = trim(($material->name ?? '') . ' ' . ($material->unique_id ?? ''));
            if (isset($datos['unitario_row_label']) && trim((string)$datos['unitario_row_label']) !== '') {
                $matName = trim((string)$matName . ' - ' . (string)$datos['unitario_row_label']);
            }
            $html .= '<td class="material-cell">
                        <span class="material-name-ellipsis">' . htmlspecialchars($matName, ENT_QUOTES, 'UTF-8') . '</span>
                    </td>';

            $html .= '<td class="text-end mono">' . number_format($num($datos['measuring'] ?? 0), 2) . ' ' . htmlspecialchars((string)$defaultUnitmeasure, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['units'] ?? 0), 2) . ' ' . htmlspecialchars((string)$defaultUnit, ENT_QUOTES, 'UTF-8') . '</td>';
            //$html .= '<td class="text-end mono">' . number_format($num($datos['quantity'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['waste'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['sq_ft'] ?? 0), 2) . ' ' . htmlspecialchars((string)$defaultUnitmeasure, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['units_total'] ?? 0), 2) . ' ' . htmlspecialchars((string)$defaultUnit, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost_ea'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['tax'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost1'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost_day'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['burden'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['lab_cost'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['days'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['cost2'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['sub_total'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['oh'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['profit'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['weather'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['total'] ?? 0), 2) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';

        // Fila FINAL de totales
        $html .= '<tfoot><tr>';
        $html .= '<td class="text-center mono" colspan="2">TOTAL</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['measuring'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['units'], 2) . '</td>';
        //$html .= '<td class="text-end mono">' . number_format($totals['quantity'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['waste'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['sq_ft'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['units_total'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost_ea'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['tax'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost1'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost_day'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['burden'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['lab_cost'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['days'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['cost2'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['sub_total'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['oh'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['profit'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['weather'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['total'], 2) . '</td>';
        $html .= '</tr></tfoot>';

        $html .= '</table></div>';
        $html .= '</section>'; // âœ… cierre del wrapper del reporte

        return $html;
    }

    #endregion total datas

}

class Total_Material
{
    public $id_material;
    public $material;
    public $measuring;
    public $op_unit = '';
    public $unit;
    public $total_units;
    public $quantity;
    public $unit_measuring;
    public $waste;
    public $total;
    public $principal = false;
    public $sq_ft;
    public $cost_ea;
    public $cost;
    public $tax;
    public $cost1;
    public $cost_day;
    public $burden;
    public $lab_cost;
    public $days;
    public $cost2;
    public $sub_total;
    public $oh;
    public $profit;
    public $weather;
}

// class Material
// {
//     public $id;
//     public $user_id;
//     public $name;
//     public $material_class_id;
//     public $material_division_id;
//     public $unique_id;
//     public $default_unit;
//     public $description;
//     public $measurement_unit;
//     public $height;
//     public $width;
//     public $length;
//     public $prices;
//     public $waste;
//     public $production_rate;
//     public $production_subed_out_cost;
//     public $cleaning_cost;
//     public $cleaning_subed_out;
//     public $associated_products;
//     public $subbed_out_rate;
//     public $created_at;
//     public $updated_at;
//     public $project_id;
//     public $material_type_id;
//     public $unit_measure_value; //campo calculado

//     public function __construct($JSON_STRING)
//     {
//         $data = json_decode($JSON_STRING, true);
//         $this->id = $data['id'];
//         $this->user_id = $data['user_id'];
//         $this->name = $data['name'];
//         $this->material_class_id = $data['material_class_id'];
//         $this->material_division_id = $data['material_division_id'];
//         $this->unique_id = $data['unique_id'];
//         $this->default_unit = $data['default_unit'];
//         $this->description = $data['description'];
//         $this->measurement_unit = $data['measurement_unit'];

//         $this->length = $data['length'];

//         $this->width = $data['width'];
//         $this->length = $data['length'];
//         $this->prices = $data['prices'];
//         $this->waste = $data['waste'];
//         $this->production_rate = $data['production_rate'];
//         $this->production_subed_out_cost = $data['production_subed_out_cost'];
//         $this->cleaning_cost = $data['cleaning_cost'];
//         $this->cleaning_subed_out = $data['cleaning_subed_out'];
//         $this->associated_products = $data['associated_products'];
//         $this->subbed_out_rate = $data['subbed_out_rate'];
//         $this->created_at = $data['created_at'];
//         $this->updated_at = $data['updated_at'];
//         $this->project_id = $data['project_id'];
//         $this->material_type_id = $data['material_type_id'];
//         $this->unit_measure_value = $this->Unit_measure_value();
//     }

    

//     public function Unit_measure_value()
//     {
//         $length=0;
//         switch ($this->material_type_id) {
            
//             case 1: //area
                
//                 $length=($this->length*$this->height)/144;
//                 break;
//             case 2: //lenght
                
//                 $length=$this->length/12;
                
//                 break;
//              case 3: //quantity
//                 $length=1;
//                 break;
//             default:
//                 $length=0;
//         }
//         return $length;
//     }
// }

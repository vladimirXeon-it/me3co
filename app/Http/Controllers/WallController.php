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

            $result = $this->processPerimeter($wall);
        }
        if ($wall->type == "opening") {

            $result = $this->processOpening($wall);
        }

        // print("<pre>" . print_r($result, true) . "</pre>");
        $wall->formData=null;
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
                     



                    //Agregar material a la cuenta
                    $Agregarmaterial = new Total_Material();
                    $Agregarmaterial->id_material = $selectedMaterial->id;
                    $Agregarmaterial->material = $selectedMaterial;
                    $Agregarmaterial->measuring = $additionalData->totalUnits;

                    $this->addtotalDatas($Agregarmaterial, $data);
                }

                $perimeterFieldsFinal[] = $additionalData;
             } catch (Exception $ex) {
                log_message('error', 'processPerimeter error: ' . $ex->getMessage());
             }
        }
        $data->perimeterFields = $perimeterFieldsFinal;


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
    
    function processArea($data)
    {
        //var_dump($data->formData);
        $data = isset($data->formData) ? json_decode($data->formData) : (object)[];
        //var_dump($data);

        // Si está guardada el área original, la preservamos en la variable
        if (isset($data->wall_total_area_original)) {
            $area_original = (float)$data->wall_total_area_original;
        }

        // 1) Si existe pitch válido → aplicar
        if (isset($data->pitch) && (float)$data->pitch > 0) {

            // Si no se ha guardado aún el área original, la guardamos
            if (!isset($data->wall_total_area_original)) {
                $data->wall_total_area_original = $data->wall_total_area;
            }

            $data->wall_total_area = $data->wall_total_area * (float)$data->pitch;
        }

        // 2) Si NO hay pitch pero SÍ hay original → restaurar
        if ((!isset($data->pitch) || $data->pitch == "" || $data->pitch == 0)
            && isset($data->wall_total_area_original)) {

            $data->wall_total_area = $data->wall_total_area_original;
        }

        $data->area_cubic_ft = $data->Area_thickness * $data->wall_total_area;
        
        $data->underlay_sq_ft = $data->wall_total_area;

        //rise_drop
        $selectedMaterial = json_decode($data->wall_material ?? 'null');
        if ($selectedMaterial != null) {

            $data->Total_units = round($data->area_cubic_ft / $selectedMaterial->unit_measure_value, 2);

            $data->rise_drop_area_added = $data->rise_drop_rise * $data->wall_total_perimeter;
            $data->rise_drop_total = $data->rise_drop_area_added * $data->rise_drop_thickness;
            $total_measuring = $data->rise_drop_total_unit =  round($data->rise_drop_total / $selectedMaterial->unit_measure_value, 2);


            //Agregar material a la cuenta
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $total_measuring;
            $this->addtotalDatas($Agregarmaterial, $data);
        }

        //underlay
        $selectedMaterial = json_decode($data->underlay_material);
        if ($selectedMaterial != null) {
            $data->underlay_total       = $data->underlay_sq_ft * $data->underlay_thickness;
            $total_measuring = $data->underlay_total_unit  =  round($data->underlay_total / $selectedMaterial->unit_measure_value, 2);


            //Agregar material a la cuenta
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $total_measuring;
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



            $additional_material = $additionalData->material;
            $selectedMaterial = json_decode($additional_material);
            if ($selectedMaterial != null) {


                $thickness   = (float) $additionalData->thickness;
                $cubicFt     = (float) $data->wall_total_area; // o lo que defina tu cubicFt
                $unitMeasure = (float) $selectedMaterial->unit_measure_value;

                $additionalData->cubicFt = $cubicFt;

                //$additionalData->cubicFt = $data->wall_total_area * $thickness;
                $total_measuring = ($cubicFt * $thickness) / ($unitMeasure > 0 ? $unitMeasure : 1);
                $additionalData->totalUnits = round($total_measuring, 2);



                //Agregar material a la cuenta
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $total_measuring;
                $this->addtotalDatas($Agregarmaterial, $data);
            }
            $additionalDatasFinal[] = $additionalData;
        }
        $data->additionalMaterials = $additionalDatasFinal;

        //$material_per_sq_ft = (object)$data->material_per_sq_ft;
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
                    $qty = $data->{"quantity_per_sq_ft[" . $index . "]"} ?? 0;

                    $total = $data->wall_total_area * $qty;
                    $total_measuring = round($total, 2);



                    //Agregar material a la cuenta
                    $Agregarmaterial = new Total_Material();
                    $Agregarmaterial->id_material = $selectedMaterial->id;
                    $Agregarmaterial->material = $selectedMaterial;
                    $Agregarmaterial->measuring = $total_measuring;

                    $this->addtotalDatas($Agregarmaterial, $data);
                }

                $additionalDatasFinal[] = $additionalData;
            } catch (Exception $ex) {
            }
        }
        $data->material_per_sq_ft = $additionalDatasFinal;


        if (isset($data->totalsDatas)) {
            $materialesAgrupados = $this->agruparMaterialesPorId($data->totalsDatas);

            //print_r($data->totalsDatas);
            //print_r($materialesAgrupados);
            $data->totales_html = $this->generarTablaHtml($materialesAgrupados);
        }
        


        $this->handleChangeadjustmentDatas($data);
        return  $data;
    }
    function processWallCalculations($data)
    {

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
        //dd($data);
        $materialesAgrupados = $this->agruparMaterialesPorId($data->totalsDatas);

        $crew = $this->addCrew($data->project_id);
        $materialesAgrupados = $this->Calcula_totalDatas1($materialesAgrupados, $data->project, $crew);

        //print_r($data->totalsDatas);
        //print_r($materialesAgrupados);
        $data->totales_html = $this->generarTablaHtml($materialesAgrupados);


        return  $data;
    }

    #region calcula material   




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
        // echo "calcula bloque 1<br>";

        $calculatedEffectiveFoundationHeight = $this->calculateFoundationHeight($updatedFormData);
        $calculatedTotalWallHeight = $this->calculatedWallHeight($updatedFormData, $calculatedEffectiveFoundationHeight);
        $calculateTotalWallLength = $this->calculationWallLength($updatedFormData);
        $calculateTotalSquareArea = $this->calculationSquareArea($calculatedTotalWallHeight, $calculateTotalWallLength);
        $calculateTotalCubicArea = $this->calculationCubicArea($updatedFormData, $calculateTotalSquareArea);
        $calculateAreaCubicYards = $this->calculationCubicYards($updatedFormData, $calculateTotalCubicArea);
        $calculateWallSquareUnits = $this->calculationWallSquareUnit($updatedFormData, $calculateTotalSquareArea);
        // $calculateWallCubicUnits = $this->calculationWallCubicUnit($updatedFormData, $calculateTotalCubicArea);
        $calculateCopingTotals = $this->calculationCopingTotal($updatedFormData);
        $calculateCopingTotalUnits = $this->calculationCopingTotalUnit($updatedFormData, $calculateCopingTotals);


        //Agregar material a la cuenta
        $Agregarmaterial = new Total_Material();
        $wall_material = json_decode($updatedFormData->wall_material);
        if ($wall_material != null) {
            $Agregarmaterial->id_material = $wall_material->id;
            $Agregarmaterial->material = $wall_material;
            $Agregarmaterial->measuring = $calculateTotalSquareArea;
            $Agregarmaterial->principal = true;
            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }
        $updatedFormData->effective_foundation_height = $calculatedEffectiveFoundationHeight;
        $updatedFormData->total_wall_height = $calculatedTotalWallHeight;
        $updatedFormData->total_wall_length = $calculateTotalWallLength;
        $updatedFormData->total_square_area = $calculateTotalSquareArea;


        $updatedFormData->total_cubic_area = $calculateTotalCubicArea;
        $updatedFormData->area_cubic_yards = $calculateAreaCubicYards;
        $updatedFormData->wall_square_units = $calculateWallSquareUnits;
        // $updatedFormData->wall_cubic_units = $calculateWallCubicUnits;
        // Wall coping material
        $updatedFormData->coping_material_total = $calculateCopingTotals;
        $updatedFormData->coping_material_total_units = $calculateCopingTotalUnits;

        //Agregar material a la cuenta
        $coping_material = $updatedFormData->coping_material;
        $selectedMaterial = json_decode($coping_material);
        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $updatedFormData->coping_material_total;
            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }

        //cambios sep 2024
        if (($updatedFormData->anchor_spacing + $updatedFormData->anchor_additional_spaces) > 0) {
            $updatedFormData->anchor_total_spaces = ($updatedFormData->total_wall_length / $updatedFormData->anchor_spacing) + $updatedFormData->anchor_additional_spaces;
        }

        $updatedFormData->anchor_total = $this->calculationTotalAnchors($updatedFormData);
        $selectedMaterial = json_decode($updatedFormData->anchor_material);
        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $updatedFormData->anchor_total;
            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }
        //cambios sep 2024

        $selectedMaterial = json_decode($updatedFormData->top_wall_material);

        $updatedFormData->total_anchor_coping =   $this->calculationTotalAnchorCoping($updatedFormData);
        $updatedFormData->total_anchor_coping_units = $this->calculationTotalAnchorCopingUnits($selectedMaterial, $updatedFormData->total_anchor_coping);
        $updatedFormData->anchor_total = $this->calculationTotalAnchors($updatedFormData);

        
        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $updatedFormData->total_anchor_coping_units;
            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }
        return $updatedFormData;
    }


    public function calculaBloque5(&$updatedFormData)
    {
        // echo "calculaBloque5<br>";
        // Calculate functions
        $calculateSpacesFilled = $this->calculateTotalSpacesFilled($updatedFormData);
        $calculateTotalLift = $this->calculateTotalLifts($updatedFormData);
        $calculateRebarLf = $this->calculateRebarLfs($updatedFormData, $calculateTotalLift);
        $calculateVericalRebarTotal = $this->calculateVericalRebarTotals($updatedFormData, $calculateSpacesFilled, $calculateRebarLf);
        $calculateRebarTon = $this->calculateRebarTons($updatedFormData, $calculateVericalRebarTotal);
        $calculateRebarPerTon = $this->calculateRebarPerTons($updatedFormData->grout_fill_material);
        $calculatePostionPerTotal = $this->calculatePostionPerTotals($updatedFormData);
        $calculatePostionOtherTotal = $this->calculatePostionOtherTotals($calculateSpacesFilled, $calculatePostionPerTotal);
        $calculateAreaGrouted = $this->calculateAreaGrouteds($updatedFormData, $calculateSpacesFilled);
        $calculateRemainingArea = $this->calculateRemainingAreas($updatedFormData, $calculateAreaGrouted);
        $calculateGroutMaterial = $this->calculateGroutMaterials($updatedFormData, $calculateAreaGrouted);
        $calculateRemainingMaterial = $this->calculateRemainingMaterials($updatedFormData, $calculateRemainingArea);
        $calculateFillMatPerCy = $this->calculateFillMatPerCys($updatedFormData);

        //Update Data
        $updatedFormData->total_spaces_filled = $calculateSpacesFilled;
        $updatedFormData->total_lifts = $calculateTotalLift;
        $updatedFormData->rebar_lf_pr_space = $calculateRebarLf;
        $updatedFormData->vertical_rebar_total = $calculateVericalRebarTotal;

        $selectedMaterial = json_decode($updatedFormData->grout_fill_material);
        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $updatedFormData->vertical_rebar_total;
            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }

        $updatedFormData->lft_rebar_per_ton = $calculateRebarPerTon;
        $updatedFormData->vertical_total_rebar_tons = $calculateRebarTon;
        $updatedFormData->vertical_postioner_per_total = $calculatePostionPerTotal;
        $updatedFormData->vertical_postioner_other_total = $calculatePostionOtherTotal;

        $selectedMaterial = json_decode($updatedFormData->other_select_material);
        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $updatedFormData->vertical_postioner_other_total;
            $this->addtotalDatas($Agregarmaterial, $updatedFormData);
        }

        $updatedFormData->vertical_grouted_area = $calculateAreaGrouted;
        $updatedFormData->remaining_area = $calculateRemainingArea;
        $updatedFormData->total_grout_mat = $calculateGroutMaterial;
        $updatedFormData->total_remaining_mat = $calculateRemainingMaterial;
        $updatedFormData->sq_fill_mat_per_cy = ((float)$updatedFormData->sq_fill_mat_per_cy_manuality > 0) ? (float)$updatedFormData->sq_fill_mat_per_cy_manuality : $calculateFillMatPerCy;

        $selectedMaterial = json_decode($updatedFormData->vertical_fill_remaining);
        if ($selectedMaterial != null) {
            $Agregarmaterial = new Total_Material();
            $Agregarmaterial->id_material = $selectedMaterial->id;
            $Agregarmaterial->material = $selectedMaterial;
            $Agregarmaterial->measuring = $updatedFormData->total_remaining_mat;
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

            $selectedMaterial = json_decode($updatedFormData->control_material);
            if ($selectedMaterial != null) {
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $updatedFormData->control_total_cj_material;
                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }


            $updatedFormData->control_total_caulking_material = $calculateTotalCaulkingMaterial;
            $selectedMaterial = json_decode($updatedFormData->control_rod);
            if ($selectedMaterial != null) {
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $updatedFormData->control_total_caulking_material;
                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }

            $updatedFormData->control_total_cj_material_ea = $calculateTotalCjMaterial_ea;
            $updatedFormData->control_total_caulking_material_ea = $calculateTotalCaulkingMaterial_ea;

            $updatedFormData->control_total_sq_ft = $calculateTotalHalfBlock;
            $selectedMaterial = json_decode($updatedFormData->half_block_material);
            if ($selectedMaterial != null) {
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $updatedFormData->control_total_sq_ft;
                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }

            $updatedFormData->total_half_unit = $calculateTotalHalfUnit;
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
        $coping_material = $updatedFormData->coping_material;
        $selectedMaterial = json_decode($coping_material);
        if ($selectedMaterial != null) {
            $materialLength = $selectedMaterial->length;
            $materialHeight = $selectedMaterial->height;
            $materialWidth = $selectedMaterial->width;
            $calculateUnit = $this->calculateCopingUnit($materialHeight, $materialLength);

            $updatedFormData->coping_material_height = $materialHeight;
            $updatedFormData->coping_material_width = $materialWidth;
            $updatedFormData->coping_material_length = $materialLength;

            $updatedFormData->coping_material_unit = $calculateUnit;
        }

        return $updatedFormData;
    }




    public function calculateWallUnit($height, $length)
    {
        // echo "calculateWallUnit<br>";
        return round(($height * $length) / 144, 3);
    }

    public function calculateWallSqUnit($wallUnit)
    {
        // echo "calculateWallSqUnit<br>";
        return round(1 / $wallUnit, 3);
    }

    public function calculateWallCubicUnit($length, $height, $width)
    {
        // echo "calculateWallCubicUnit<br>";
        $wallCubicArea = $length * $height * $width;
        return round(1 / ($wallCubicArea * 1728), 3);
    }




    public function calculateCopingUnit($height, $length)
    {
        // echo "calculateCopingUnit<br>";
        return round(($height * $length) / 144, 3);
    }

    public function calculateRebarUnit($height, $length)
    {
        // echo "calculateRebarUnit<br>";
        return round(($height * $length) / 144, 3);
    }

    public function calculateRebarSqUnit($rebarUnit)
    {
        // echo "calculateRebarSqUnit<br>";
        return round(1 / $rebarUnit, 3);
    }

    public function calculateHalfBlockUnit($height, $length)
    {
        // echo "calculateHalfBlockUnit<br>";
        return round(($height * $length) / 144, 3);
    }

    public function calculateHalfBlockSqUnit($halfBlockUnit)
    {
        // echo "calculateHalfBlockSqUnit<br>";
        return round(1 / $halfBlockUnit, 3);
    }

    public function calculateFoundationHeight($data)
    {
        // echo "calculateFoundationHeight<br>";
        return floatval(floatval($data->finish_floor) - floatval($data->top_of_footing));
    }

    public function calculatedWallHeight($data, $effectiveFoundation)
    {
        // echo "calculatedWallHeight<br>";
        return $effectiveFoundation + $data->wall_height;
    }

    public function calculationWallLength($data)
    {
        // echo "calculationWallLength<br>";
        //echo $data->wall_length;

        $riseDrop = ($data->rise_drop === "rise") ? $data->rise_value : $data->drop_value;
        return floatval($data->wall_length) + floatval($riseDrop);
    }

    public function calculationSquareArea($totalWallHeight, $totalWallLength)
    {
        // echo "calculationSquareArea<br>";
        return $totalWallHeight * $totalWallLength;
    }

    public function calculationCubicArea($data, $totalSqArea)
    {
        // echo "calculationCubicArea<br>";
        return $data->wall_structure_thickness * $totalSqArea;
    }

    public function calculationCubicYards($data, $totalCubicArea)
    {
        // echo "calculationCubicYards<br>";
        return round($totalCubicArea / 27, 2);
    }

    public function calculationWallSquareUnit($data, $totalSqArea)
    {
        // echo "calculationWallSquareUnit<br>";
        return round($data->wall_material_square_unit * $totalSqArea, 3);
    }

    public function calculationCopingTotal($data)
    {
        // echo "calculationCopingTotal<br>";
        return round(floatval($data->wall_length) * floatval($data->coping_material_quantity), 2);
    }

    public function calculationCopingTotalUnit($data, $total)
    {
        // echo "calculationCopingTotalUnit<br>";
        if ($data->coping_material_length > 0) {
            return round($total / ($data->coping_material_length / 12), 2);
        }
        return 0;
    }

    public function calculationTotalAnchors($data)
    {
        // echo "calculationTotalAnchors<br>";
        if ($data->anchor_total_spaces > 0 && $data->anchor_quantity > 0) {
            return round($data->anchor_total_spaces * $data->anchor_quantity, 2);
        }
        return 0;
    }

    public function calculationTotalAnchorCoping($data)
    {
        // echo "calculationTotalAnchorCoping<br>";
        if ($data->coping_material_length > 0)
            if ($data->top_wall_material != null) {

                // Crear el objeto Material
                //$selectedMaterial = new Material($data->top_wall_material);
                $selectedMaterial = json_decode($data->top_wall_material);
                return round($data->wall_length * ($data->coping_wall_side), 2);
            }


        return 0;
    }

    public function calculationTotalAnchorCopingUnits($data, $total)
    {
        //echo $data;

        return round( $total / 20, 2);
        //return round( $total / 1, 2);
    }

    public function calculateTotalSpacesFilled($data)
    {
        // echo "calculateTotalSpacesFilled<br>";
        if ($data->rebar_spacing > 0 && $data->additional_spacing > 0) {
            return $data->wall_length / $data->rebar_spacing + $data->additional_spacing;
        }
        return 0;
    }

    public function calculateTotalLifts($data)
    {
        // echo "calculateTotalLifts<br>";
        if ($data->rebar_lift_spaces > 0)
            return round($data->total_wall_height / $data->rebar_lift_spaces, 3);

        return 0;
    }

    public function calculateRebarLfs($data, $totalLifts)
    {
        // echo "calculateRebarLfs<br>";
        return round(($data->rebar_lift_spaces + $data->vertical_rebar_overlap) * $totalLifts, 3);
    }

    public function calculateVericalRebarTotals($data, $spacesFilled, $totalRebar)
    {
        // echo "calculateVericalRebarTotals<br>";
        return round($spacesFilled * $totalRebar * $data->bars_per_space, 3);
    }

    public function calculateRebarTons($data, $totalRebarLfts)
    {

        try {
            return round($totalRebarLfts / $data->lft_rebar_per_ton, 3);
        } catch (\Throwable  $ex) {
            // echo "totalRebarLfts " . $totalRebarLfts . "<br>";
            // echo "data->lft_rebar_per_ton " . $data->lft_rebar_per_ton . "<br>";
        }

        // echo "calculateRebarTons<br>";
        return 0;
    }

    public function calculateRebarPerTons($data)
    {

        try {
            $selectedMaterial = json_decode($data);
            return round($selectedMaterial->shortton_wlf, 3);
        } catch (\Throwable  $ex) {
            // echo "totalRebarLfts " . $totalRebarLfts . "<br>";
            // echo "data->lft_rebar_per_ton " . $data->lft_rebar_per_ton . "<br>";
        }

        // echo "calculateRebarTons<br>";
        return 0;
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
        return round($spacesFilled * $positionPerTotal, 3);
    }

    public function calculateAreaGrouteds($data, $spacesFilled)
    {
        // echo "calculateAreaGrouteds<br>";
        return round($spacesFilled * $data->total_wall_height * 0.66, 3);
    }

    public function calculateRemainingAreas($data, $areaGrouted)
    {
        // echo "calculateRemainingAreas<br>";
        $total_sq_area_filled = 0;
        foreach ($data->courses as $course) {
            $course =  (object) $course;

            $total_sq_area_filled += $course->total_sq_area_filled;
        }
        
        return round($data->total_square_area - $total_sq_area_filled - $data->vertical_grouted_area, 2);
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
        return round($data->total_wall_height * $totalCjSpaces, 2);
    }

    public function calculateTotalCaulkingMaterials($data, $totalCjMaterials)
    {
        // echo "calculateTotalCaulkingMaterials<br>";
        return round($data->control_rod_side * $totalCjMaterials, 2);
    }

    public function calculateTotalCjMaterials_ea($data, $totalCjSpaces)
    {
        // echo "calculateTotalCjMaterials_ea<br>";
        $selectedMaterial = json_decode($data->control_material);
        $control_material_length = $selectedMaterial->length / 12;
        try {

            return round($data->control_total_cj_material / $control_material_length, 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalCaulkingMaterials_ea($data, $totalCjMaterials)
    {
        // echo "calculateTotalCaulkingMaterials_ea<br>";
        $selectedMaterial = json_decode($data->control_rod);
        $control_material_length = $selectedMaterial->length / 12;
        try {

            return round(($data->control_total_cj_material * $data->control_rod_side) / $control_material_length, 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalHalfBlocks($data, $totalCjMaterials, $materialLength = null)
    {
        // echo "calculateTotalHalfBlocks<br>";
        $halfLength = isset($materialLength) ? $materialLength : $data->half_block_length;

        try {
            return round($totalCjMaterials * ($halfLength / 12), 2);
        } catch (\Throwable $th) {
            //throw $th;
            return 0;
        }
    }

    public function calculateTotalHalfUnits($data, $halfBlock, $materialUnit = null)
    {
        // echo "calculateTotalHalfUnits<br>";
        $halfUnit = isset($materialUnit) ? $materialUnit : $data->half_block_lf_unit;

        try {

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
        $courses_new = [];
        $courses = $formData->courses;


        foreach ($courses as $selectedCourseData) {

            $selectedCourseData = (object)$selectedCourseData;
            // echo "selectedCourseData->name" . $selectedCourseData->name . "<br>";



            //top_elevation
            $selectedCourseData->top_elevation =  round(
                $selectedCourseData->band_height +
                    $selectedCourseData->bottom_elevation,
                3
            );



            $selectedCourseData->total_material_units = $this->calculateTotalMaterialUnits($formData, $selectedCourseData);
            $selectedCourseData->material_sq_ft = floatval($formData->wall_length * $selectedCourseData->band_height);
            //Agregar material a la cuenta


            $band_material = (object)$selectedCourseData->band_material;
            $Agregarmaterial = new Total_Material();
            if (isset($band_material->id)) {

                $Agregarmaterial->id_material = $band_material->id;
                $Agregarmaterial->material = $band_material;
                $Agregarmaterial->measuring = $selectedCourseData->material_sq_ft;
                $Agregarmaterial->principal = true;
                $this->addtotalDatas($Agregarmaterial, $formData);

                //total_courses
                $band_material_height_feet = round(($band_material->height / 12), 2);
                $total_courses = round(($selectedCourseData->band_height / $band_material_height_feet), 0);
                $selectedCourseData->total_courses = $total_courses;
            }
            $rebar_material = (object)$selectedCourseData->rebar_material;
            $Agregarmaterial = new Total_Material();
            if (isset($rebar_material->id)) {

                $Agregarmaterial->id_material = $rebar_material->id;
                $Agregarmaterial->material = $rebar_material;
                $Agregarmaterial->measuring = $selectedCourseData->material_sq_ft;
                $Agregarmaterial->principal = true;
                $this->addtotalDatas($Agregarmaterial, $formData);


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
            }







            //Agregar material a la cuenta
            $fill_material = (object)$selectedCourseData->fill_material;
            $Agregarmaterial = new Total_Material();
            if (isset($fill_material->id)) {

                $selectedMaterial = json_decode($formData->wall_material);
                $selectedCourseData->sq_grouted_per_cy = $selectedMaterial->sq_ft_per_cy;
                $selectedCourseData->total_sq_fill_materials = $this->calcularTotalSqFillMaterials($selectedCourseData, $selectedMaterial->sq_ft_per_cy);
                $Agregarmaterial->id_material = $fill_material->id;
                $Agregarmaterial->material = $fill_material;
                $Agregarmaterial->measuring = $selectedCourseData->total_sq_fill_materials;
                $Agregarmaterial->principal = true;
                $this->addtotalDatas($Agregarmaterial, $formData);
            }
            if (isset($selectedCourseData->sq_grouted_per_cy_manuality)) {
                $selectedCourseData->sq_grouted_per_cy = ((float)$selectedCourseData->sq_grouted_per_cy_manuality > 0) ? (float)$selectedCourseData->sq_grouted_per_cy_manuality : $selectedCourseData->sq_grouted_per_cy;
            }
            $selectedCourseData->area_grouted_sq = $this->calculateGroutedSqs($formData, $selectedCourseData);
            $selectedCourseData->total_grout_cy =  $this->calculateGroutedCys($selectedCourseData, $selectedCourseData->area_grouted_sq);
            $selectedCourseData->total_area_grout_sq = $this->calculateTotalGroutedCys($selectedCourseData, $selectedCourseData->total_grout_cy);







            $courses_new[] = $selectedCourseData;
        }
        $formData->courses = $courses_new;


        return $formData;
    }
    public function calculateTotalMaterialUnits($data, $course)
    {
        // echo "calculateTotalMaterialUnits<br>";
        $wallLength = floatval($data->wall_length);
        $wallMaterialUnit = floatval($data->wall_material_unit);
        $bandHeight = floatval($course->band_height);
        $materialUnits = round(($wallLength * $bandHeight) / $wallMaterialUnit, 0);
        return $materialUnits;
    }

    public function calculateTotalRebars($data, $course)
    {
        // echo "calculateTotalRebars<br>";
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
    }

    public function calculateRebarCourses($data, $course)
    {
        // echo "calculateRebarCourses<br>";
        $bandHeight = floatval($course->band_height);
        $wallMaterialHeight = floatval($data->material_height);
        $totalCourse = round($bandHeight / ($wallMaterialHeight / 12), 3);
        return $totalCourse;
    }

    public function calculateBandTotalRebarLfs($course, $rebarCourse, $totalRebarLength)
    {
        // echo "calculateBandTotalRebarLfs<br>";
        $rebarQuantity = floatval($course->rebar_quantity);
        $totalRebarLf = round(floatval($totalRebarLength) * $rebarQuantity * $rebarCourse, 3);
        return $totalRebarLf;
    }

    public function calculateGroutedSqs($data, $course)
    {
        // echo "calculateGroutedSqs<br>";
        $wallLength = floatval($data->wall_length);
        $bandHeight = floatval($course->band_height);
        $groutedSq = round($wallLength * $bandHeight, 3);
        return $groutedSq;
    }

    public function calculateGroutedCys($course, $groutedSq)
    {
        // echo "calculateGroutedCys<br>";
        // echo "course" . $course->sq_grouted_per_cy . "<br>";
        // echo "groutedSq" . $groutedSq . "<br>";

        $groutedPrCy = floatval($course->sq_grouted_per_cy);
        $groutedCy = round($groutedPrCy * $groutedSq, 3);
        return $groutedCy;
    }

    public function calculateTotalGroutedCys($course, $groutedCy)
    {
        // echo "calculateTotalGroutedCys<br>";
        $totalGroutedCy = $groutedCy;
        return $totalGroutedCy;
    }







    public function calcularTotalRebarLength($formData, $selectedCourseData, $rebar_material)
    {
        //   echo "calcularTotalRebarLength\n";

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
    }

    public function calcularTotalRebarUnits($formData, $rebar_material)
    {
        //   echo "calcularTotalRebarUnits\n"; 
        $total_rebar_units = round(((float)$formData->total_rebar_linear_feet) /
            ($rebar_material->unit_measure_value), 2);

        return $total_rebar_units;
    }

    public function calcularTotalRebarLinearFeet($formData)
    {
        //   echo "calcularTotalRebarLinearFeet\n";

        $totalRebarLength = isset($formData->total_rebar_length) ? (float)$formData->total_rebar_length : 0;
        $rebarQuantity = isset($formData->rebar_quantity) ? (float)$formData->rebar_quantity : 0;
        $totalCourses = isset($formData->total_courses) ? (float)$formData->total_courses : 0;

        $totalLinearFeet = number_format($totalRebarLength * $rebarQuantity * $totalCourses, 0);

        //   echo "totalRebarLength: $totalRebarLength\n";
        //   echo "rebarQuantity: $rebarQuantity\n";
        //   echo "totalCourses: $totalCourses\n";
        //   echo "totalLinearFeet: $totalLinearFeet\n";

        return $totalLinearFeet;
    }

    public function calcularRebarLfTon($rebar_material)
    {
        //   echo "calcularRebarLfTon\n";


        $shortTonWlf = isset($rebar_material->shortton_wlf) ? (float)$rebar_material->shortton_wlf : 0;
        $totalTon = round($shortTonWlf, 2);


        return $totalTon;
    }

    public function calcularSqFtFilledGrouted($formData, $wallLength)
    {
        //   echo "calcularSqFtFilledGrouted\n";

        $bandHeight = isset($formData->band_height) ? (float)$formData->band_height : 0;


        $total = round($bandHeight * $wallLength, 3);

        //   echo "bandHeight: $bandHeight\n";
        //   echo "wallLength: $wallLength\n";
        //   echo "sqFtFilledGrouted: $total\n";

        return $total;
    }

    public function calcularDeductedAreaVertically($formData, $total_spaces_filled)
    {
        //   echo "calcularDeductedAreaVertically\n";

        $bandHeight = isset($formData->band_height) ? (float)$formData->band_height : 0;


        $total = round($bandHeight * $total_spaces_filled * 0.66, 3);

        //    echo "bandHeight: $bandHeight\n";
        //    echo "totalSpacesFilled: $total_spaces_filled \n";
        //    echo "deductedAreaVertically: $total\n";

        return $total;
    }

    public function calcularTotalSqAreaFilled($formData)
    {
        //   echo "calcularTotalSqAreaFilled\n";

        $sqFtFilledGrouted = isset($formData->sq_ft_filled_grouted) ? (float)$formData->sq_ft_filled_grouted : 0;
        $deductedAreaVertically = isset($formData->deducted_area_vertically) ? (float)$formData->deducted_area_vertically : 0;

        $total = round($sqFtFilledGrouted - $deductedAreaVertically, 2);

        //   echo "sqFtFilledGrouted: $sqFtFilledGrouted\n";
        //   echo "deductedAreaVertically: $deductedAreaVertically\n";
        //   echo "totalSqAreaFilled: $total\n";

        return $total;
    }

    public function calcularTotalSqFillMaterials($formData, $sqGroutedPerCy)
    {
        //   echo "calcularTotalSqFillMaterials\n";

        $totalSqAreaFilled = $formData->total_sq_area_filled;



        $total = ($sqGroutedPerCy > 0) ?  round($totalSqAreaFilled / $sqGroutedPerCy, 2) : round($totalSqAreaFilled / 90, 2);
        //   echo "totalSqAreaFilled: $totalSqAreaFilled\n";
        //   echo "sqGroutedPerCy: $sqGroutedPerCy\n";
        //   echo "totalSqFillMaterials: $total\n";

        return $total;
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

        $additionalDatas = (object)$updatedFormData->additionalDatas;

        $additionalDatasFinal = [];

        foreach ($additionalDatas as $index  =>  $additionalData) {
            $total_measuring = 0;
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
                    $additionalData->spacing_total_units = $additionalData->spacing_total_ft / ($materialLength / 12);

                    $total_measuring = $additionalData->spacing_total_ft;
                }


                if ($additionalData->additional_type === "quantity") {
                    $total_measuring = $additionalData->total_unit_quantity;
                }

                if ($additionalData->additional_type === "lineal") {
                    $total_measuring = $additionalData->lineal_total_ft;
                }
                if ($additionalData->additional_type === "pr_sq_ft") {
                    $total_measuring = $additionalData->total_lineal_per_sq_ft;
                }
                //Agregar material a la cuenta
                $Agregarmaterial = new Total_Material();
                $Agregarmaterial->id_material = $selectedMaterial->id;
                $Agregarmaterial->material = $selectedMaterial;
                $Agregarmaterial->measuring = $total_measuring;
                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }
            $additionalDatasFinal[] = $additionalData;
        }
        $updatedFormData->additionalDatas = $additionalDatasFinal;
        return $updatedFormData;
    }


    public function handleChangeadjustmentDatas(&$updatedFormData)
    {
        // echo "handleChange<br>";
        /*  $name = $event->name;
        $value = $event->value;
        preg_match('/\[(\d+)\]/', $name, $matches);
        $index = $matches ? (int) $matches[1] : 0;
        $indexedName = preg_replace('/\[\d+\]/', '', $name); */
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
                $this->addtotalDatas($Agregarmaterial, $updatedFormData);
            }
            $adjustmentDatasFinal[] = $adjustmentData;
        }
        $updatedFormData->adjustmentDatas = $adjustmentDatasFinal;
        return $updatedFormData;
    }

    public function calculateLinealUnits($index, $data, $additionalDatas)
    {
        // echo "calculateLinealUnits<br>";

        $materialLength = (float) $additionalDatas->additional_material_length;
        $linealQuantity = (float) $additionalDatas->lineal_quantity;
        $wallLength = (float) $data->wall_length;
        $total_units = round($wallLength / ($materialLength / 12), 2);
        return $total_units;
    }


    public function calculateLinealTotalOverlaps($index, $data, $additionalDatas)
    {
        // echo "calculateLinealTotalOverlaps<br>";
        // $additionalDatas=(object)$data->additionalDatas[$index];
        $additional_material =  json_decode($additionalDatas->additional_material);

        $materialLength = (float) $data->total_wall_height;
        $linealOverlap = (float) $additionalDatas->lineal_unit_overlap;
        $totalOverlap = round($materialLength  + $linealOverlap, 2);
        return $totalOverlap;
    }

    public function calculateTotalLinealFts($index, $data, $totalUnits, $totalOverlap)
    {
        // echo "calculateTotalLinealFts<br>";
        $additionalDatas = (object)$data->additionalDatas[$index];
        $linealQuantity = (float)  $additionalDatas->lineal_quantity;
        $totallinealFt = round($linealQuantity * $totalUnits * $totalOverlap, 2);
        return $totallinealFt;
    }

    public function calculateTotalLinealUnits($index, $data, $totallineal, $additionalDatas)
    {
        // echo "calculateTotalLinealUnits<br>";

        $materialLength = (float)  $additionalDatas->additional_material_length;
        $totalLinealUnit = round($additionalDatas->lineal_total_ft / ($materialLength / 12), 2);
        return $totalLinealUnit;
    }

    public function calculateSpacingTotals($index, $data, $additionalDatas)
    {
        // echo "calculateSpacingTotals<br>";
        // $additionalDatas=(object)$data->additionalDatas[$index];
        $totalSpacing = 0;
        $wallLength = (float) $data->total_wall_length;
        $space = (float) $additionalDatas->spacing_space;
        if ($space > 0) {
            $totalSpacing = round($wallLength / $space, 2);
        }

        return $totalSpacing;
    }



    public function calculateTotalUnitSqfts(&$additionalDatas, $total_square_area)
    {
        // echo "calculateTotalUnitSqfts<br>";

        $wallArea = (float) $total_square_area;
        $UnitSqft = (float) $additionalDatas->unit_per_sq_ft;
        $total_lineal_sqft = round($wallArea * $UnitSqft, 2);
        $materialLength = (float) $additionalDatas->additional_material_length;
        $materialLength_ft = $materialLength / 12;
        $additionalDatas->total_lineal_per_sq_ft = $total_lineal_sqft;
        $totalUnitSq = $total_lineal_sqft / $materialLength_ft;
        return $totalUnitSq;
    }

    public function calculateTotalquantity1($index, $data)
    {
        // echo "calculateTotalquantity1<br>";
        $additionalDatas = (object)$data->additionalDatas[$index];
        $total_unit_quantity = (float) isset($additionalDatas->quantity) ? $additionalDatas->quantity : 0;
        return $total_unit_quantity;
    }

    public function calculateTotalTopBottom($index, $updatedFormData, $additionalDatas)
    {
        // echo "calculateTotalTopBottom<br>";

        $top_bottom_spaces = (float) ($additionalDatas->top_bottom_spaces ?? 0);
        $top_bottom_total_spaces = $updatedFormData->wall_length * $top_bottom_spaces;
        $additionalDatas->top_bottom_total_spaces = $top_bottom_total_spaces;
        $top_bottom_total_units = round(($top_bottom_total_spaces /$updatedFormData->wall_material_unit), 3);
        $additionalDatas->top_bottom_total_units = $top_bottom_total_units;
    
        return $top_bottom_total_units;
    }

    public function calculatePerArea($index, $updatedFormData, $additionalDatas)
    {
        // echo "calculatePerArea<br>";

        $pr_area_sides = (float) ($additionalDatas->pr_area_sides ?? 0);
        $total_lineal_pr_area = $updatedFormData->total_square_area * $pr_area_sides;
        $additionalDatas->total_lineal_pr_area = $total_lineal_pr_area;
        $total_unit_pr_area = round(($total_lineal_pr_area /$updatedFormData->wall_material_unit), 3);
        $additionalDatas->total_unit_pr_area = $total_unit_pr_area;
    
        return $total_unit_pr_area;
    }


    #endregion additional datas



    #region total datas

    public function addtotalDatas($Total_Material, $updatedFormData)
    {
        //  $totalsDatasFinal = (array)$updatedFormData->totalsDatas;

        $this->totalsDatasFinal[] = $Total_Material;
        $updatedFormData->totalsDatas = $this->totalsDatasFinal;
        return $updatedFormData;
    }

    public function Calcula_totalDatas(&$updatedFormData)
    {
        // echo "handleChange<br>";
        /*  $name = $event->name;
        $value = $event->value;
        preg_match('/\[(\d+)\]/', $name, $matches);
        $index = $matches ? (int) $matches[1] : 0;
        $indexedName = preg_replace('/\[\d+\]/', '', $name); */

        $totalsDatas = (object)$updatedFormData->totalsDatas;



        foreach ($totalsDatas as $index  =>  $additionalData) {

            $additionalData = (object)$additionalData;
            //dd($additionalData);
            // $additionalDatas->additionalDatas[$index]=$additionalData;
            // echo "index " . $index . "<br>";
            // echo "additionalData " . json_encode($additionalData) . "<br>";




            $totalsDatasFinal[] = $additionalData;
        }
        $updatedFormData->totalsDatas = $totalsDatasFinal;
        return $updatedFormData;
    }
    public function Calcula_totalDatas1(&$updatedFormData, $project, $crew)
    {
        $laborInfoArray = $crew[0]['labor_info'] ?? [];
        if (!is_array($laborInfoArray)) {
            $laborInfoArray = [];
        }

        $crew_hours_total = 0;
        $crew_cost_total = 0;
        $percentage_total = 0;
        $price_total = 0;
        $quantity = 0;
        $hours_per_day = 0;

        foreach ($laborInfoArray as $li) {
            //dd($li);
            $quantity = isset($li['quantity']) ? (float)$li['quantity'] : 0;
            $hours_per_day = isset($li['hours_per_day']) ? (float)$li['hours_per_day'] : 0;

            $crew_cost_total   += isset($li['crew_cost']) ? (float)$li['crew_cost'] : 0;
            $percentage_total  += isset($li['percentage_total']) ? (float)$li['percentage_total'] : 0;
            $price_total       += isset($li['price_total']) ? (float)$li['price_total'] : 0;
        }

        //dd($quantity);

        foreach ($updatedFormData as $index  =>  $additionalData) {

            //$additionalData = (object)$additionalData;
            //dd($additionalData['material']);
            $fraction_sq_ft = ($additionalData['material']['height']/12); 
            $additionalData['quantity'] = round(($additionalData['measuring']/$fraction_sq_ft), 2);
            $additionalData['waste'] = round(($additionalData['quantity'] * ($additionalData['material']['waste']/100)), 2);
            $additionalData['sq_ft'] = round(($additionalData['quantity'] + $additionalData['waste']), 2);
            $additionalData['units'] = round(($additionalData['sq_ft']*($additionalData['quantity']/$additionalData['measuring'])), 2);
            $additionalData['cost_ea'] = round(($additionalData['material']['prices']), 2);
            $additionalData['cost'] = round(($additionalData['units'] * $additionalData['cost_ea']), 2);
            $additionalData['tax'] = round(($additionalData['cost'] * ($project['tax']/100)), 2);
            $additionalData['cost1'] = round(($additionalData['cost'] + $additionalData['tax']), 2);
            $additionalData['days'] = ($additionalData['units'] > 0 && $quantity > 0 && $additionalData['material']['production_rate'] > 0)  ? ($additionalData['units']/$quantity)/$additionalData['material']['production_rate'] : 0;
            $additionalData['cost_day'] = $crew_cost_total * $additionalData['days'];
            $additionalData['burden'] = $percentage_total * $additionalData['days'];
            $additionalData['lab_cost'] = $additionalData['cost_day'] + $additionalData['burden'];
            $additionalData['sub_total'] = $additionalData['cost'] + $additionalData['cost1'] + $additionalData['lab_cost'] + $additionalData['cost2'];
            $additionalData['oh'] = round(($additionalData['sub_total'] * ($project['oh']/100)), 2);
            $additionalData['profit'] = round(($additionalData['sub_total'] * ($project['profit']/100)), 2);
            $additionalData['weather'] = round(($additionalData['sub_total'] * ($project['weather']/100)), 2);
            $additionalData['total'] = $additionalData['sub_total'] + $additionalData['oh'] + $additionalData['profit'];


           $totalsDatasFinal[] = $additionalData;
        }
        $updatedFormData = $totalsDatasFinal;
        //dd($updatedFormData);
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
                $quantity     = isset($item['quantity']) ? (float)$item['quantity'] : 0;

                $payPerDayPerPerson = $costPerHour * $hoursPerDay;

                // Costo del crew para ese labor_type (por día)
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
                        // Caso A: price es fijo por crew (por día)
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
    function agruparMaterialesPorId($materiales)
    {
        $resultadosAgrupados = [];

        foreach ($materiales as $material) {
            // Aseguramos que el material tiene un id_material válido
            $material = (object)$material;
            if (isset($material->id_material)) {
                $id = $material->id_material;

                // Si no existe el material en el arreglo agrupado, inicializarlo
                if (!isset($resultadosAgrupados[$id])) {
                    $resultadosAgrupados[$id] = [
                        'material' => $material->material,
                        'total' => 0,
                        'measuring' => 0,
                        'quantity' => 0,

                        'waste'      => 0,
                        'sq_ft'      => 0,
                        'units'      => 0,
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
                $resultadosAgrupados[$id]['quantity'] += isset($material->quantity) ? $material->quantity : 0;

                $resultadosAgrupados[$id]['waste']     += (float) ($material->waste ?? 0);
                $resultadosAgrupados[$id]['sq_ft']     += (float) ($material->sq_ft ?? 0);
                $resultadosAgrupados[$id]['units']     += (float) ($material->units ?? 0);

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
                $resultadosAgrupados[$id]['weather']    += (float) ($material->profit ?? 0);
                $resultadosAgrupados[$id]['total']     += (float) ($material->total ?? 0);
            }
        }

        return $resultadosAgrupados;
    }
    function generarTablaHtml($materialesAgrupados)
    {
        // Helpers locales
        $num = function ($v) {
            return is_numeric($v) ? (float)$v : 0.0;
        };

        // Columnas a sumar (todas las numéricas que imprimes)
        $sumCols = [
            'measuring', 'quantity', 'waste', 'sq_ft', 'units',
            'cost_ea', 'cost', 'tax', 'cost1',
            'cost_day', 'burden', 'lab_cost', 'days',
            'cost2', 'sub_total', 'oh', 'profit', 'weather', 'total'
        ];

        // Inicializa totales
        $totals = array_fill_keys($sumCols, 0.0);

        // Estilos (sin depender de tu layout; si ya tienes Bootstrap, se verá mejor)
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

            /* Aún más chico en pantallas grandes */
            @media (min-width: 992px){
            .xeon-table th.col-id,
            .xeon-table td.col-id{
                width: 50px;
                max-width: 50px;
                min-width: 50px;
                font-size: 11px;
            }
            }
        </style>';

        $html .= '<div class="xeon-table-wrap">';
        $html .= '<table class="xeon-table">';
        $html .= '<thead>
            <tr>
                <th class="col-id">ID Material</th>
                <th>Material</th>
                <th class="text-end">Measuring</th>
                <th class="text-end">Quantity</th>
                <th class="text-end">Waste</th>
                <th class="text-end">SQ FT</th>
                <th class="text-end">Units</th>
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
            $html .= '<td class="text-end mono">' . number_format($num($datos['quantity'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['waste'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['sq_ft'] ?? 0), 2) . '</td>';
            $html .= '<td class="text-end mono">' . number_format($num($datos['units'] ?? 0), 2) . '</td>';
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
        $html .= '<td class="text-end mono">' . number_format($totals['quantity'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['waste'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['sq_ft'], 2) . '</td>';
        $html .= '<td class="text-end mono">' . number_format($totals['units'], 2) . '</td>';
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
    }

    #endregion total datas

}

class Total_Material
{
    public $id_material;
    public $material;
    public $measuring;
    public $unit;
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

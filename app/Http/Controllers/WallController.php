<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LineTemplate;
use App\Models\Wall;
use Exception;
use PhpParser\node\Expr\Cast\Object_;

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
        $materialesAgrupados = $this->agruparMaterialesPorId($data->totalsDatas);

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
        $updatedFormData->sq_fill_mat_per_cy = $calculateFillMatPerCy;

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

            $selectedCourseData->material_sq_ft =  round(
                2 + 2,
                3
            );



            $selectedCourseData->total_material_units = $this->calculateTotalMaterialUnits($formData, $selectedCourseData);
            $selectedCourseData->Material_sq_ft = $formData->wall_length * $selectedCourseData->band_height;
            //Agregar material a la cuenta


            $band_material = (object)$selectedCourseData->band_material;
            $Agregarmaterial = new Total_Material();
            if (isset($band_material->id)) {

                $Agregarmaterial->id_material = $band_material->id;
                $Agregarmaterial->material = $band_material;
                $Agregarmaterial->measuring = $selectedCourseData->Material_sq_ft;
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
                $Agregarmaterial->measuring = $selectedCourseData->Material_sq_ft;
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

                $additionalData->total_unit_per_sq_ft = $calculateTotalUnitSqft;
                $additionalData->total_unit_quantity = $calculateTotalquantity;


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
            // $additionalDatas->additionalDatas[$index]=$additionalData;
            // echo "index " . $index . "<br>";
            // echo "additionalData " . json_encode($additionalData) . "<br>";




            $totalsDatasFinal[] = $additionalData;
        }
        $updatedFormData->totalsDatas = $totalsDatasFinal;
        return $updatedFormData;
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
                    ];
                }

                // Sumar los valores de total, measuring y quantity
                $resultadosAgrupados[$id]['total'] += isset($material->total) ? $material->total : 0;
                $resultadosAgrupados[$id]['measuring'] += isset($material->measuring) ? $material->measuring : 0;
                $resultadosAgrupados[$id]['quantity'] += isset($material->quantity) ? $material->quantity : 0;
            }
        }

        return $resultadosAgrupados;
    }
    function generarTablaHtml($materialesAgrupados)
    {
        // Comienzo de la tabla
        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<thead>
                <tr>
                    <th>ID Material</th>
                    <th>Material</th>
                    <th>Measuring</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
              </thead>
              <tbody>';

        // Generar filas para cada material
        foreach ($materialesAgrupados as $idMaterial => $datos) {
            $datos['material'] = (object)$datos['material'];
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($idMaterial) . '</td>';
            $html .= '<td>' . htmlspecialchars($datos['material']->name) . " " . htmlspecialchars($datos['material']->unique_id) . '</td>';
            $html .= '<td>' . number_format($datos['measuring'], 2) . '</td>';
            $html .= '<td>' . number_format($datos['quantity'], 2) . '</td>';
            $html .= '<td>' . number_format($datos['total'], 2) . '</td>';
            $html .= '</tr>';
        }

        // Cierre de la tabla
        $html .= '</tbody></table>';

        // Devolver el HTML generado
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWallsTable extends Migration
{
    public function up()
    {
        Schema::create('walls', function (Blueprint $table) {
            $table->id();
            $table->json('additional_datas'); // Use JSON column type for additionalDatas
            $table->json('courses')->nullable();
            $table->string('name')->nullable();
            $table->string('wall_material_value');
            $table->string('trade')->nullable();
            $table->string('shape')->nullable();
            $table->string('gable_value')->nullable();
            $table->string('slope_value')->nullable();
            $table->string('rise_drop')->nullable();
            $table->string('rise_value');
            $table->string('drop_value');
            $table->string('wall_height');
            $table->string('finish_floor');
            $table->string('top_of_footing');
            $table->integer('effective_foundation_height');
            $table->integer('total_wall_height');
            $table->string('wall_length');
            $table->string('total_wall_length');
            $table->string('wall_structure_thickness');
            $table->integer('total_square_area');
            $table->integer('total_cubic_area');
            $table->string('wall_square_units');
            $table->string('wall_cubic_units')->nullable();
            $table->string('coping_material');
            $table->string('coping_material_quantity');
            $table->string('coping_material_total');
            $table->string('coping_material_total_units');
            $table->string('anchor_material');
            $table->string('anchor_spacing');
            $table->string('anchor_quantity');
            $table->string('anchor_total');
            $table->string('top_wall_material');
            $table->string('coping_wall_side');
            $table->string('total_anchor_coping');
            $table->string('total_anchor_coping_units');
            $table->string('bars_per_ton');
            $table->string('grout_fill_material');
            $table->string('rebar_spacing');
            $table->string('additional_spacing');
            $table->integer('total_spaces_filled');
            $table->string('rebar_lift_spaces');
            $table->string('total_lifts');
            $table->string('vertical_rebar_overlap');
            $table->string('rebar_lf_pr_space');
            $table->string('bars_per_space');
            $table->string('vertical_rebar_total');
            $table->string('lft_rebar_per_ton');
            $table->string('vertical_total_rebar_tons');
            $table->string('vertical_rebar_positioner');
            $table->string('vertical_postioner_per_total');
            $table->string('vertical_postioner_other_total');
            $table->string('vertical_grout_material');
            $table->string('vertical_grouted_area');
            $table->string('remaining_area');
            $table->string('vertical_fill_remaining')->nullable();
            $table->string('sq_fill_mat_per_cy');
            $table->string('total_grout_mat');
            $table->string('total_remaining_mat');
            $table->string('control_material');
            $table->string('control_spacing');
            $table->string('control_rod');
            $table->string('control_rod_side');
            $table->string('half_block_material');
            $table->string('control_total_cj_spaces');
            $table->string('control_total_cj_material');
            $table->string('control_total_cj_material_ea');
            $table->string('control_total_caulking_material');
            $table->string('control_total_caulking_material_ea');
            $table->string('control_total_sq_ft');
            $table->string('total_half_unit');
            $table->text('note')->nullable();
            $table->string('half_block_lf_unit');
            $table->string('half_block_sq_unit');
            $table->float('half_block_length');
            $table->integer('material_height');
            $table->integer('material_width');
            $table->integer('material_length');
            $table->string('wall_material_unit');
            $table->string('wall_material_square_unit');
            $table->float('wall_material_cubic_unit');
            $table->integer('coping_material_height');
            $table->integer('coping_material_width');
            $table->integer('coping_material_length');
            $table->string('coping_material_unit');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('walls');
    }
}
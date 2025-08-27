<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('material_class_id');
            $table->integer('material_division_id');
            $table->string('unique_id');
            $table->text('description');
            $table->string('measurement_unit');
            $table->float('height');
            $table->float('width');
            $table->float('length');
            $table->text('prices');
            $table->float('waste');
            $table->float('production_rate');
            $table->float('production_subed_out_cost');
            $table->float('cleaning_cost');
            $table->float('cleaning_subed_out');
            $table->text('associated_products');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materials');
    }
}

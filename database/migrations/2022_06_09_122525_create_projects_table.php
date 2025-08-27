<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->text('name');
            $table->text('estimetor');
            $table->text('address');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('zip');
            $table->text('customer');
            $table->string('bid_number');
            $table->string('bid_date');
            $table->string('bid_time');
            $table->text('materials');
            $table->text('crews');
            $table->text('equipments');
            $table->text('items');
            $table->integer('equipment_id');
            $table->integer('labor_id');
            $table->text('material_profit_info');
            $table->text('labor_profit_info');
            $table->text('equipment_profit_info');
            $table->text('subcontractor_profit_info');
            $table->text('other_profit_info');
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
        Schema::dropIfExists('projects');
    }
}

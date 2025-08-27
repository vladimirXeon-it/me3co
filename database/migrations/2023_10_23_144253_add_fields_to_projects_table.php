<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('job_walk_date')->nullable();
            $table->time('job_walk_time')->nullable();
            $table->date('rfi_due_date')->nullable();
            $table->time('rfi_due_time')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('size')->nullable();
            $table->string('measurement_unit')->nullable();
            $table->string('architect')->nullable();
            $table->text('description')->nullable();
            $table->string('budgeting_type')->nullable();
            $table->string('competitive_bidding')->nullable();
            $table->string('project_type')->nullable();
            $table->string('client')->nullable();
            $table->date('bid_to_client_date')->nullable();
            $table->time('bid_to_client_time')->nullable();
            $table->string('account_manager')->nullable();
            $table->double('project_value')->nullable();
            $table->double('fee_percentage')->nullable();
            $table->string('market_sector')->nullable();
            $table->string('certificate')->nullable();
            $table->string('certifying_agency')->nullable();
            $table->text('notes')->nullable();
            $table->string('owning_office')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['job_walk_date', 'job_walk_time', 'rfi_due_date', 'rfi_due_time', 'start_date', 'end_date', 'size', 'measurement_unit', 'architect', 'description', 'invitation_type', 'project_type', 'client', 'bid_to_client_date', 'bid_to_client_time', 'account_manager', 'project_value', 'fee_percentage', 'market_sector', 'certificate', 'certifying_agency', 'notes', 'owning_office']);
        });
    }
}

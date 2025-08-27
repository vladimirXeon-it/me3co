<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('walls', function (Blueprint $table) {
            $table->decimal('area_cubic_yards', 12, 3)->nullable();
        });
    }

    public function down() {
        Schema::table('walls', function (Blueprint $table) {
            $table->dropColumn('area_cubic_yards');
        });
    }
};

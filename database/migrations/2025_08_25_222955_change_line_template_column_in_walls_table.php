<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('walls', function (Blueprint $table) {
            $table->json('lineTemplate')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('walls', function (Blueprint $table) {
            $table->text('lineTemplate')->nullable()->change();
        });
    }
};

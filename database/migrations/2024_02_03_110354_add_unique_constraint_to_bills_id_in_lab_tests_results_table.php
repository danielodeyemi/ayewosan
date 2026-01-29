<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lab_tests_results', function (Blueprint $table) {
            $table->unique('bills_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_tests_results', function (Blueprint $table) {
            $table->dropUnique('lab_tests_results_bills_id_unique');
        });
    }
};

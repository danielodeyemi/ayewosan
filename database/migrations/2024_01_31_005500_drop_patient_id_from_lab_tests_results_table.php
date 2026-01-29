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
            $table->dropForeign('lab_tests_results_patient_id_foreign');
            $table->dropColumn('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_tests_results', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->after('id');
            $table->foreign('patient_id')->references('id')->on('patients');
        });
    }
};

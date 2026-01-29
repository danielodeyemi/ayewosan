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
        Schema::table('bills_labtests_pivot', function (Blueprint $table) {
            $table->renameColumn('lab_test_id', 'lab_tests_id'); // Rename the column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills_labtests_pivot', function (Blueprint $table) {
            $table->renameColumn('lab_tests_id', 'lab_test_id'); // Rename the column back
        });
    }
};

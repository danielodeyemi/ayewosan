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
            $table->renameColumn('bill_id', 'bills_id'); // Rename the column
        });
    
        Schema::table('patient_transactions', function (Blueprint $table) {
            $table->renameColumn('bill_id', 'bills_id'); // Rename the column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills_labtests_pivot', function (Blueprint $table) {
            $table->renameColumn('bills_id', 'bill_id'); // Rename the column back
        });
    
        Schema::table('patient_transactions', function (Blueprint $table) {
            $table->renameColumn('bills_id', 'bill_id'); // Rename the column back
        });
    }
};

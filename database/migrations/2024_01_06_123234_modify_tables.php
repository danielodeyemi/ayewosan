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
        Schema::table('bills', function (Blueprint $table) {// Drop foreign key constraint
            $table->dropColumn('patients_transaction_id'); // Drop the column
        });

        Schema::table('bills_labtests_pivot', function (Blueprint $table) {
            $table->dropForeign(['bills_id']); // Drop foreign key constraint
            $table->dropForeign(['lab_tests_id']); // Drop foreign key constraint
            $table->renameColumn('bills_id', 'bill_id'); // Rename the column
            $table->renameColumn('lab_tests_id', 'lab_test_id'); // Rename the column
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade'); // Add foreign key constraint
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('cascade'); // Add foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->unsignedBigInteger('patients_transaction_id')->nullable(); // Add the column
            $table->unsignedBigInteger('test_id'); // Add the column
        });

        Schema::table('bills_labtests_pivot', function (Blueprint $table) {
            $table->dropForeign(['bill_id']); // Drop foreign key constraint
            $table->dropForeign(['lab_test_id']); // Drop foreign key constraint
            $table->renameColumn('bill_id', 'bills_id'); // Rename the column
            $table->renameColumn('lab_test_id', 'lab_tests_id'); // Rename the column
            $table->foreign('bills_id')->references('id')->on('bills')->onDelete('cascade'); // Add foreign key constraint
            $table->foreign('lab_tests_id')->references('id')->on('lab_tests')->onDelete('cascade'); // Add foreign key constraint
        });
    }
};

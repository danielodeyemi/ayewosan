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
        Schema::table('referral_transactions', function (Blueprint $table) {
            // $table->dropForeign(['bill_id']); // If there's a foreign key constraint
            $table->dropColumn('bill_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('bill_id')->nullable();
            // $table->foreign('bill_id')->references('id')->on('bills'); // If you want to re-add the foreign key constraint
        });
    }
};

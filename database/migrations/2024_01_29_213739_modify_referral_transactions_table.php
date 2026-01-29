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
            $table->dropColumn('credit_amount');
            $table->dropColumn('debit_amount');
            $table->decimal('ref_amount', 10, 2)->default(0);
            $table->enum('type', ['Credit', 'Debit']);
            $table->enum('payment_method', ['Cash', 'Bank Transfer', 'POS', 'Monthly Bill Payment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_transactions', function (Blueprint $table) {
            $table->decimal('credit_amount', 10, 2)->default(0);
            $table->decimal('debit_amount', 10, 2)->default(0);
            $table->dropColumn('ref_amount');
            $table->dropColumn('type');
            $table->dropColumn('payment_method');
        });
    }
};

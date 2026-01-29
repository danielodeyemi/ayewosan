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
            $table->renameColumn('account_balance', 'before_payout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_transactions', function (Blueprint $table) {
            $table->renameColumn('before_payout', 'account_balance');
        });
    }
};

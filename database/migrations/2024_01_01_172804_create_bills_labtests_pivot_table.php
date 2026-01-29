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
        Schema::create('bills_labtests_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bills_id')->constrained()->onDelete('cascade');
            $table->foreignId('lab_tests_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills_labtests_pivot');
    }
};

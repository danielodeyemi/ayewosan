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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('birth_date');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('phone_number')->nullable();
            $table->string('patient_email')->nullable();
            $table->text('patient_address')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('referrer_id')
                ->nullable();
            $table->foreign('referrer_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};

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
        Schema::create('lab_tests_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->timestamps();
        });

        Schema::create('lab_tests_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->unsignedBigInteger('lab_tests_categories_id')->nullable();
            $table->timestamps();

            $table->foreign('lab_tests_categories_id')
                ->references('id')
                ->on('lab_tests_categories')
                ->onDelete('cascade');
        });

        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_tests_groups_id');
            $table->string('name', 255);
            $table->string('code', 255);
            $table->longText('test_description')->nullable();
            $table->decimal('production_cost', 10, 2)->default(0.00);
            $table->decimal('patient_price', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('lab_tests_groups_id')
                ->references('id')
                ->on('lab_tests_groups')
                ->onDelete('cascade');
        });

        Schema::create('lab_tests_results_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->longText('template_content');
            $table->timestamps();
        });

        Schema::create('lab_tests_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bills_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('result_template_id')->nullable();
            $table->dateTime('report_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->unsignedBigInteger('delivered_by')->nullable();
            $table->longText('report_description')->nullable();
            $table->text('report_remarks')->nullable();
            $table->enum('result_status', ['Test Pending', 'Result Recorded', 'Result Delivered']);
            $table->timestamps();

            $table->foreign('bills_id')
                ->references('id')
                ->on('bills')
                ->onDelete('cascade');

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');

            $table->foreign('result_template_id')
                ->references('id')
                ->on('lab_tests_results_templates')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_tests_categories', 'lab_tests_groups', 'lab_tests', 'lab_tests_results_templates', 'lab_tests_results');
    }
};

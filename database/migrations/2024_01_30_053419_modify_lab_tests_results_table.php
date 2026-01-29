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
            $table->dropForeign(['result_template_id']); // drop foreign key

            $table->dropColumn('result_template_id'); // drop the column

            $table->renameColumn('report_date', 'result_date'); // rename column

            $table->renameColumn('delivery_date', 'delivery_date_time'); // rename column

            $table->dropColumn('delivery_time'); // drop the column

            $table->renameColumn('report_description', 'result_content'); // rename column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_tests_results', function (Blueprint $table) {
            $table->string('result_template_id')->nullable(); // add the column back
            
            $table->foreign('result_template_id')->references('id')->on('result_templates'); // add the foreign key back

            $table->renameColumn('result_date', 'report_date'); // rename column back

            $table->renameColumn('delivery_date_time', 'delivery_date'); // rename column back

            $table->time('delivery_time')->nullable(); // add the column back

            $table->renameColumn('result_content', 'report_description'); // rename column back
        });
    }
};

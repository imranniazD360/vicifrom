<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Call-center registry — ParameterController centerMatch via closer code
 * (CRM: centerlist_tb).
 */
class CreateViciformCentersTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('viciform_centers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('center_name', 150)->nullable();
            $table->string('center_code', 150)->nullable()->index();
            $table->string('employee_id', 150)->nullable();
            $table->string('created_by', 300)->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viciform_centers');
    }
}

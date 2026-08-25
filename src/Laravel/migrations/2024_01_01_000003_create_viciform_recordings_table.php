<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recording links from Vicidial MP3 URLs (CRM: recordings).
 */
class CreateViciformRecordingsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('viciform_recordings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('webform_lead_id')->nullable()->index();
            $table->string('recording_filename')->nullable();
            $table->unsignedBigInteger('recording_id')->nullable();
            $table->text('recording_link')->nullable();
            $table->string('status')->nullable()->default('pending');
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viciform_recordings');
    }
}

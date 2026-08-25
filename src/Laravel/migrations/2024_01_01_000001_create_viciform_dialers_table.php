<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dialer registry — ParameterController dialerMatch lookup
 * (CRM: dialerlist_tb).
 */
class CreateViciformDialersTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('viciform_dialers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dialer_ip', 150)->nullable()->index();
            $table->string('dialer_weblink', 255)->nullable();
            $table->string('dialer_access', 300)->nullable();
            $table->string('dialer_no', 40)->nullable()->index();
            $table->string('dialer_team', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viciform_dialers');
    }
}

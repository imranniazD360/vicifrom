<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Full Vicidial campaign-script / ParameterController + Avatar store payload
 * (CRM: avatar_temp_leads + avatar_leads combined).
 */
class CreateViciformWebformLeadsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('viciform_webform_leads', function (Blueprint $table) {
            $table->bigIncrements('id');

            // App / agent
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('agent_name')->nullable();
            $table->string('agent_email')->nullable();
            $table->string('fullname')->nullable();
            $table->string('verifier_name')->nullable();
            $table->string('closer_name')->nullable();

            // Lead identity
            $table->string('lead_id')->nullable()->index();
            $table->string('vendor_id')->nullable()->index();
            $table->string('list_id')->nullable()->index();
            $table->string('entry_list_id')->nullable();
            $table->string('source_id')->nullable();
            $table->string('rank')->nullable();
            $table->string('owner')->nullable();
            $table->string('ownern')->nullable();
            $table->string('called_count')->nullable();
            $table->string('entry_date')->nullable();

            // Contact
            $table->string('gmt_offset_now')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('phone_number')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_initial')->nullable();
            $table->string('last_name')->nullable();
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->text('address3')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country_code')->nullable();
            $table->string('gender')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('security_phrase')->nullable();
            $table->text('comments')->nullable();

            // Agent session (script-passed)
            $table->string('user')->nullable();
            $table->string('pass')->nullable();
            $table->string('orig_pass')->nullable();
            $table->string('phone_login')->nullable();
            $table->string('original_phone_login')->nullable();
            $table->string('phone_pass')->nullable();
            $table->string('fronter')->nullable();
            $table->string('user_group')->nullable();
            $table->string('session_id')->nullable();
            $table->string('session_name')->nullable();
            $table->string('agent_log_id')->nullable()->index();

            // Campaign / call
            $table->string('campaign')->nullable()->index();
            $table->string('list_name')->nullable();
            $table->text('list_description')->nullable();
            $table->string('closer')->nullable()->index();
            $table->string('group_a')->nullable();
            $table->string('channel_group')->nullable();
            $table->string('dispo')->nullable()->index();
            $table->string('INOUT')->nullable();
            $table->string('SQLdate')->nullable();
            $table->string('epoch')->nullable();
            $table->string('uniqueid')->nullable()->index();
            $table->string('call_id')->nullable();
            $table->string('closecallid')->nullable();
            $table->string('xfercallid')->nullable();
            $table->string('dialed_number')->nullable();
            $table->string('dialed_label')->nullable();
            $table->string('parked_by')->nullable();

            // Telephony
            $table->string('server_ip')->nullable()->index();
            $table->string('customer_server_ip')->nullable();
            $table->string('customer_zap_channel')->nullable();
            $table->string('SIPexten')->nullable();

            // Scripts
            $table->string('camp_script')->nullable();
            $table->string('in_script')->nullable();
            $table->string('in_script_two')->nullable();
            $table->string('script_width')->nullable();
            $table->string('script_height')->nullable();

            // Recordings
            $table->string('recording_filename')->nullable();
            $table->unsignedBigInteger('recording_id')->nullable();
            $table->text('recording_link')->nullable();

            // Custom / presets / DID
            $table->text('user_custom_one')->nullable();
            $table->text('user_custom_two')->nullable();
            $table->text('user_custom_three')->nullable();
            $table->text('user_custom_four')->nullable();
            $table->text('user_custom_five')->nullable();
            $table->string('preset_number_a')->nullable();
            $table->string('preset_number_b')->nullable();
            $table->string('preset_number_c')->nullable();
            $table->string('preset_number_d')->nullable();
            $table->string('preset_number_e')->nullable();
            $table->string('preset_dtmf_a')->nullable();
            $table->string('preset_dtmf_b')->nullable();
            $table->string('did_id')->nullable();
            $table->string('did_extension')->nullable();
            $table->string('did_pattern')->nullable();
            $table->string('did_description')->nullable();
            $table->text('did_custom_one')->nullable();
            $table->text('did_custom_two')->nullable();
            $table->text('did_custom_three')->nullable();
            $table->text('did_custom_four')->nullable();
            $table->text('did_custom_five')->nullable();

            // Login vars / misc
            $table->string('email_row_id')->nullable();
            $table->string('LOGINvarONE')->nullable();
            $table->string('LOGINvarTWO')->nullable();
            $table->string('LOGINvarTHREE')->nullable();
            $table->string('LOGINvarFOUR')->nullable();
            $table->string('LOGINvarFIVE')->nullable();
            $table->string('hide_relogin_fields')->nullable();
            $table->text('web_vars')->nullable();

            // CRM xfer extras (Avatar / Parameter store)
            $table->string('center')->nullable();
            $table->string('dailer_no')->nullable();
            $table->string('dialer_no')->nullable();
            $table->string('dialer_id')->nullable();
            $table->string('dialername')->nullable();
            $table->string('centername')->nullable();
            $table->string('smoker')->nullable();
            $table->string('age')->nullable();
            $table->string('xferSubmission')->nullable();
            $table->string('status')->nullable()->default('pending')->index();

            // Unknown extras JSON blob
            $table->json('extras')->nullable();

            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viciform_webform_leads');
    }
}

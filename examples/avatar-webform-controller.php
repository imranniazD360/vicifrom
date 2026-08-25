<?php

declare(strict_types=1);

/**
 * AvatarController-style Vicidial webform bridge (copy into your app).
 *
 * Vicidial campaign script example:
 *   https://crm.example.com/leads/create?lead_id=--A--lead_id--B--&phone_number=--A--phone_number--B--&server_ip=--A--server_ip--B--&recording_filename=--A--recording_filename--B--&campaign=--A--campaign--B--&uniqueid=--A--uniqueid--B--&SIPexten=--A--SIPexten--B--&dispo=--A--dispo--B--
 *
 * Routes:
 *   Route::get('/leads/create', [AvatarWebformController::class, 'create'])->name('leads.create');
 *   Route::post('/leads/store', [AvatarWebformController::class, 'store'])->name('leads.store');
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Viciform\Laravel\ViciformFacade as Viciform;

class AvatarWebformController
{
    /**
     * Vicidial opens this URL with campaign-script query params.
     */
    public function create(Request $request)
    {
        $payload = Viciform::webform($request);

        $recording_link = $payload->recordingUrl(); // http://{ip}/RECORDINGS/MP3/{file}-all.mp3

        // Pass everything the script sent into your Blade view (display / xfer form).
        return view('display', array_merge($payload->forView(), [
            'recording_link' => $recording_link,
            'closer_code' => $payload->closerCode(),
            // Resolve dialers/centers in your app, e.g.:
            // 'dialerMatch' => DialerList::where('dialer_ip', $payload->serverIp())->value('dialer_no'),
        ]));
    }

    /**
     * Persist the xfer form submission (map to your own models).
     */
    public function store(Request $request)
    {
        $payload = Viciform::webform($request);

        $data = $payload->toArray();
        $data['recording_link'] = $payload->recordingUrl()
            ?: $request->input('recording_link');

        // Example: save into your table / model
        // AvatarLead::create($data);

        return response()->json([
            'ok' => true,
            'lead_id' => $payload->leadId(),
            'phone_number' => $payload->phoneNumber(),
            'recording_link' => $data['recording_link'],
            'fields' => $data,
        ], 201);
    }
}

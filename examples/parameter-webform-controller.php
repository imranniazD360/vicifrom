<?php

declare(strict_types=1);

/**
 * ParameterController-style Vicidial display / store (copy into your app).
 *
 * Replaces the long ParameterController::create / display / store input lists.
 *
 * Routes (register these — CRM ParameterController currently has no routes):
 *   Route::get('/display', [ParameterWebformController::class, 'display'])->name('display');
 *   Route::get('/parameters/create', [ParameterWebformController::class, 'create'])->name('parameters.create');
 *   Route::post('/parameters/store', [ParameterWebformController::class, 'store'])->name('parameters.store');
 *
 * Vicidial script URL:
 *   https://crm.example.com/display?lead_id=--A--lead_id--B--&phone_number=--A--phone_number--B--&server_ip=--A--server_ip--B--&recording_filename=--A--recording_filename--B--&closer=--A--closer--B--&campaign=--A--campaign--B--&uniqueid=--A--uniqueid--B--&SIPexten=--A--SIPexten--B--&...
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Viciform\Laravel\ViciformFacade as Viciform;
use Viciform\Webform\ParameterBridge;

class ParameterWebformController
{
    /**
     * Full ParameterController::display equivalent.
     */
    public function display(Request $request)
    {
        $payload = Viciform::webform($request);

        // Same logic as:
        //   DialerList::where('dialer_ip', $server_ip)->value('dialer_no')
        //   CenterList::where('centerCode', $closercode)->value('centerName')
        $view = ParameterBridge::forDisplay($payload, [
            'currentDateTime' => now(),
            // 'verifiers' => User::where('type', 'Closer')->get(),
            'dialer_resolver' => function ($serverIp) {
                // return DialerList::where('dialer_ip', $serverIp)->value('dialer_no');
                return null;
            },
            'center_resolver' => function ($closerCode) {
                // return CenterList::where('centerCode', $closerCode)->value('centerName');
                return null;
            },
            // Or static maps:
            // 'dialer_map' => ['10.0.0.5' => 'D1'],
            // 'center_map' => ['clo' => 'Main Center'],
        ]);

        return view('display', $view);
    }

    /**
     * ParameterController::create equivalent (subset + agents/verifiers).
     */
    public function create(Request $request)
    {
        $payload = Viciform::webform($request);

        $view = ParameterBridge::forDisplay($payload, [
            // 'agents' => User::where('type', 'avatar')->pluck('name', 'id'),
            // 'verifiers' => User::where('type', 'Closer')->get(),
        ]);

        return view('display', $view);
    }

    /**
     * ParameterController::store equivalent — map onto your model.
     */
    public function store(Request $request)
    {
        $payload = Viciform::webform($request);
        $data = ParameterBridge::forStore($payload);

        // AvatarLead::create($data);
        // $lead->recordingLink = $data['recordingLink'];

        return redirect()
            ->route('display', ['lead_id' => $payload->leadId()])
            ->with('success', 'Avatar lead created successfully.');
    }
}

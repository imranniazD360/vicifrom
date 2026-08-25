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
use Viciform\Laravel\WebformLead;
use Viciform\Laravel\Dialer;
use Viciform\Laravel\Center;
use Viciform\Laravel\Recording;
use Viciform\Webform\ParameterBridge;

class ParameterWebformController
{
    /**
     * Full ParameterController::display equivalent.
     */
    public function display(Request $request)
    {
        $payload = Viciform::webform($request);

        $view = ParameterBridge::forDisplay($payload, [
            'currentDateTime' => now(),
            // 'verifiers' => User::where('type', 'Closer')->get(),
            'dialer_resolver' => function ($serverIp) {
                return Dialer::matchNo($serverIp);
            },
            'center_resolver' => function ($closerCode) {
                return Center::matchName($closerCode);
            },
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
            'dialer_resolver' => function ($serverIp) {
                return Dialer::matchNo($serverIp);
            },
            'center_resolver' => function ($closerCode) {
                return Center::matchName($closerCode);
            },
        ]);

        return view('display', $view);
    }

    /**
     * ParameterController::store equivalent — uses viciform_webform_leads migration.
     */
    public function store(Request $request)
    {
        $payload = Viciform::webform($request);
        $data = ParameterBridge::forStore($payload);
        $data['extras'] = $payload->extras();

        $lead = WebformLead::fromWebform($data);

        if (!empty($data['recording_link'])) {
            Recording::create([
                'webform_lead_id' => $lead->id,
                'recording_filename' => $data['recording_filename'] ?? null,
                'recording_id' => $data['recording_id'] ?? null,
                'recording_link' => $data['recording_link'],
                'status' => 'saved',
            ]);
        }

        return redirect()
            ->route('display', ['lead_id' => $payload->leadId()])
            ->with('success', 'Avatar lead created successfully.');
    }
}

<?php

declare(strict_types=1);

/**
 * Laravel controller example (copy into your app).
 *
 * Route::post('/leads', [LeadController::class, 'store']);
 */

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Viciform\Exceptions\ViciformException;
use Viciform\Laravel\ViciformFacade as Viciform;

class LeadController
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'comments' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $response = Viciform::addLead([
                'phone' => $validated['phone'],
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'vendor_lead_code' => 'WEB-' . $request->ip() . '-' . time(),
            ]);
        } catch (ViciformException $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 502);
        }

        if ($response->isSuccess()) {
            return response()->json([
                'ok' => true,
                'lead_id' => $response->leadId(),
            ], 201);
        }

        if ($response->isDuplicate()) {
            return response()->json([
                'ok' => true,
                'duplicate' => true,
                'message' => $response->message(),
                'lead_id' => $response->leadId(),
            ], 200);
        }

        return response()->json([
            'ok' => false,
            'error' => $response->message(),
        ], 422);
    }
}

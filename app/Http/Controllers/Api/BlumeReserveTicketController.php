<?php

namespace App\Http\Controllers\Api;

use App\Events\BlumeReserveTicketCreated;
use App\Events\BlumeReserveTicketDeleted;
use App\Events\BlumeReserveTicketUpdated;
use App\Http\Controllers\Controller;
use App\Models\BlumeReserveTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class BlumeReserveTicketController extends BaseController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $brts = BlumeReserveTicket::where('user_id',  $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->responseApi(true, "BRTs retrieved successfully", $brts, 200);
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'reserved_amount' => 'required|numeric|min:0|max:1000000',
                'status' => 'sometimes|in:pending,active,expired',
                'expiry_date' => 'sometimes|date|after:today'
            ]);

            // Generate unique BRT code
            $brtCode = 'BRT-' . $user->id . '-' . Str::upper(Str::random(6));

            $brt = BlumeReserveTicket::create([
                'user_id' => $user->id,
                'brt_code' => $brtCode, // Use brt_code instead of code
                'reserved_amount' => $validatedData['reserved_amount'],
                'status' => $validatedData['status'] ?? 'pending',
                'expiry_date' => $validatedData['expiry_date'] ?? now()->addMonths(6)
            ]);

            event(new BlumeReserveTicketCreated($brt));

            return $this->responseApi(true, "BRT created successfully", $brt, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();

        $brt = BlumeReserveTicket::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$brt) {
            return $this->responseApi(false, "BRT not found", null, 404);
        }

        //Add Expiry tag
        $brt->checkAndUpdateStatus();

        return $this->responseApi(true, "BRT retrieved successfully", $brt, 200);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();

        $brt = BlumeReserveTicket::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$brt) {
            return $this->responseApi(false, "BRT not found", null, 404);
        }

        try {
            $validatedData = $request->validate([
                'reserved_amount' => 'sometimes|numeric|min:0|max:1000000',
                'status' => 'sometimes|in:pending,active,expired',
                'expiry_date' => 'sometimes|date|after:today'
            ]);

            $brt->update($validatedData);

            // Send Broadcast
            event(new BlumeReserveTicketUpdated($brt));

            return $this->responseApi(true, "BRT updated successfully", $brt, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        $brt = BlumeReserveTicket::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$brt) {
            return $this->responseApi(false, "BRT not found", null, 404);
        }

        $brt->delete();

        event(new BlumeReserveTicketDeleted($brt));

        return $this->responseApi(true, "BRT deleted successfully", null, 200);
    }
}

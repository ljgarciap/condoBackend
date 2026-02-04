<?php

namespace App\Http\Controllers;

use App\Models\AdminPayment;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AdminPayment::with('apartment');

        if ($user->isResident()) {
            $resident = \App\Models\Resident::where('person_id', $user->person_id)->first();
            $query->where('apartment_id', $resident->apartment_id);
        }

        return $query->latest()
            ->paginate($request->input('per_page', 10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:pending,paid,overdue',
        ]);

        $payment = AdminPayment::create($validated);

        return response()->json($payment->load('apartment'), 201);
    }

    public function show(AdminPayment $adminPayment)
    {
        return $adminPayment->load('apartment');
    }

    public function update(Request $request, AdminPayment $adminPayment)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric',
            'due_date' => 'sometimes|date',
            'paid_date' => 'nullable|date',
            'status' => 'sometimes|string|in:pending,paid,overdue',
        ]);

        $adminPayment->update($validated);

        return response()->json($adminPayment);
    }

    public function destroy(AdminPayment $adminPayment)
    {
        $adminPayment->delete();
        return response()->json(null, 204);
    }
}

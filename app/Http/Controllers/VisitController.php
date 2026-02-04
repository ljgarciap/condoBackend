<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Visit;
use App\Models\Person;
use Carbon\Carbon;

class VisitController extends Controller
{
    public function index()
    {
        return Visit::with(['person', 'apartment'])->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document' => 'required|string',
            'document_type' => 'required|string|in:CC,TI,TE,PAS,PEP,RC',
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'apartment_id' => 'required|exists:apartments,id',
            'reason' => 'nullable|string',
        ]);

        $person = Person::updateOrCreate(
            ['document' => $validated['document']],
            [
                'name' => $validated['name'],
                'document_type' => $validated['document_type'],
                'phone' => $request->phone,
                'email' => $request->email
            ]
        );

        $visit = Visit::create([
            'person_id' => $person->id,
            'apartment_id' => $validated['apartment_id'],
            'entry_at' => Carbon::now(),
            'reason' => $request->reason
        ]);

        return response()->json($visit->load(['person', 'apartment']), 201);
    }

    public function update(Request $request, Visit $visit)
    {
        $visit->update([
            'exit_at' => Carbon::now()
        ]);

        return response()->json($visit->load(['person', 'apartment']));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Visit;
use App\Models\Person;
use Carbon\Carbon;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $query = Visit::with(['person', 'apartment'])->latest();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('person', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('document', 'like', "%{$search}%");
                })->orWhereHas('apartment', function($sq) use ($search) {
                    $sq->where('number', 'like', "%{$search}%")
                      ->orWhere('block', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(block, number) LIKE ?", ["%{$search}%"]);
                });
            });
        }

        return $query->paginate($request->input('per_page', 5));
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

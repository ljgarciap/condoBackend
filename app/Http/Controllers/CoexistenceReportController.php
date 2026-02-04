<?php

namespace App\Http\Controllers;

use App\Models\CoexistenceReport;
use Illuminate\Http\Request;

class CoexistenceReportController extends Controller
{
    public function index()
    {
        return CoexistenceReport::with('apartment')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'blocks_entry' => 'boolean',
        ]);

        $report = CoexistenceReport::create($validated);

        return response()->json($report, 201);
    }

    public function show(CoexistenceReport $coexistenceReport)
    {
        return $coexistenceReport->load('apartment');
    }

    public function update(Request $request, CoexistenceReport $coexistenceReport)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string',
            'blocks_entry' => 'sometimes|boolean',
        ]);

        $coexistenceReport->update($validated);

        return response()->json($coexistenceReport);
    }

    public function destroy(CoexistenceReport $coexistenceReport)
    {
        $coexistenceReport->delete();
        return response()->json(null, 204);
    }
}

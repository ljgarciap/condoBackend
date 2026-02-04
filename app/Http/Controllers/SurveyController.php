<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        return Survey::where('is_active', true)->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'questions' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $survey = Survey::create($validated);

        return response()->json($survey, 201);
    }

    public function show(Survey $survey)
    {
        return $survey;
    }

    public function update(Request $request, Survey $survey)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'questions' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $survey->update($validated);

        return response()->json($survey);
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();
        return response()->json(null, 204);
    }
}

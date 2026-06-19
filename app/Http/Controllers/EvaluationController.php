<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function showGraph()
    {
        return view('evaluation_graph');
    }

    public function viewEvaluation($id)
    {
        return view('view_evaluation', compact('id'));
    }

    public function submitEvaluation(Request $request)
    {
        // TODO: implement evaluation submission
        return redirect()->back()->with('status', 'Evaluation submitted successfully.');
    }

    public function showEvaluations()
    {
        return view('evaluations');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EvaluationFormController extends Controller
{
    public function sendEvaluationForms()
    {
        // TODO: implement sending evaluation forms
        return redirect()->back()->with('status', 'Evaluation forms sent.');
    }
}

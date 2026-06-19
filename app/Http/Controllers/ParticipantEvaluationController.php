<?php



namespace App\Http\Controllers;

use App\Models\EvaluationQuestion;
use Exception;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipantEvaluationController extends Controller


{
    public function store(Request $request, $id)
    {
        $request->validate([
            'Question' => 'required',
            'Section' => 'required',
//        'Event_id' => 'required',
            'Type' => 'required',
        ]);

        // Extract the form data from the $request object


        // Extract the form data from the $request object
        $question = $request->input('Question');
        $section = $request->input('Section');
        $type = $request->input('Type');
        $event_id = $id;
        $options = $request->input('options', []);
        $speakers = $request->input('speakers', []);

        // Insert data into the participant_evaluation table
        $questionId = DB::table('participant_evaluation')->insertGetId([
            'questions' => $question,
            'section' => $section,
            'type' => $type,
            'event_id' => $event_id,
        ]);

        // Insert options into the options table and build CSV
        $optionValues = [];
        foreach ($options as $optionGroup) {
            foreach ($optionGroup as $optionValue) {
                $optionValues[] = $optionValue;
                DB::table('options')->insert([
                    'question_id' => $questionId,
                    'value' => $optionValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Sync CSV options column on participant_evaluation
        if (!empty($optionValues)) {
            DB::table('participant_evaluation')->where('id', $questionId)->update([
                'options' => implode(',', $optionValues),
                'updated_at' => now(),
            ]);
        }

        // Insert speakers into the speakers table
        foreach ($speakers as $speakerName) {
            DB::table('speakers')->insert([
                'question_id' => $questionId,
                'event_id' => $event_id,
                'name' => $speakerName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }



// Redirect back with success message

        return redirect()->route('evaluate-here', ['id' => $event_id])->with('message', 'Question added successfully');
    }
  




    public function index(Request $request) {
        try {
            if($request->ajax()) {
                $evaluationQuestions = EvaluationQuestion::all();

                $totalEvaluationQuestions = count($evaluationQuestions);

                return Helper::DataTableResponse($evaluationQuestions, $totalEvaluationQuestions, $totalEvaluationQuestions, isset($request->draw) ? $request->draw : '');
            }
        }
        catch (Exception $exception) {

        }

        $title = "Evaluation Questions For $request->reference";
        $data = compact("title");
        return view("Event_evaluation.list", $data);
    }

    public function report(Request $request) {
        try {

        }
        catch (Exception $exception) {

        }

        return view("");
    }
}



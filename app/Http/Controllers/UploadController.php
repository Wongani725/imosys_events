<?php
namespace App\Http\Controllers;

use App\Jobs\sendEvaluationEmails;
use App\Mail\Evaluation;
use App\Mail\EmailCertificates;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Jobs\SendPaymentReminderEmail;
use App\Jobs\SendRegistrationReminderEmail;

set_time_limit(300);
class UploadController extends Controller
{
    public function importData1(Request $request)
    {
        $event_id = $request->input('event_id');
//        dd($event_id);

        $getEmails = DB::table('event_participants')
            ->select('participant', 'balance', 'email_address', 'event_id', 'reference_code')
            ->where('event_id', $event_id)
            ->where('balance', 0)
            ->whereNotNull('email_address')
            ->where('email_address', '!=', '')
            ->get();

//        dd($getEmails);

        $chunkSize = 20;

        foreach ($getEmails->chunk($chunkSize) as $chunk) {
            foreach ($chunk as $participant) {
                if ($participant->balance <= 0) {
                    dispatch(new sendEvaluationEmails($participant));
                } else {
                    Log::info('Balance is not 0; email not sent for ' . $participant->email_address);
                }
            }
        }

        return back()->with('message', 'Email Evaluations sent successfully');
    }


    public function importData(Request $request)
    {
        $event_id = $request->event_id;
//        dd($event_id);


        /** Not Approved Bookers */
        $unapprovedBookers = DB::table('bookers')
            ->where('event_id', $event_id)
            ->where('booking_status', '!=', 'Approved')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
//        dd($unapprovedBookers);

        foreach ($unapprovedBookers->chunk(20) as $chunk) {
            foreach ($chunk as $booker) {
                dispatch(new SendPaymentReminderEmail($booker));
            }
        }

        /** Members not in bookers */
        $registeredEmails = DB::table('bookers')
            ->where('event_id', $event_id)
            ->pluck('email')
            ->filter()
            ->toArray();
//        dd($registeredEmails);


        $nonRegisteredMembers = DB::table('members')
            ->whereNotIn('email_address', $registeredEmails)
            ->whereNotNull('email_address')
            ->where('email_address', '!=', '')
            ->get();
//        dd($nonRegisteredMembers);

        foreach ($nonRegisteredMembers->chunk(20) as $chunk) {
            foreach ($chunk as $member) {
                dispatch(new SendRegistrationReminderEmail($member, $event_id));
            }
        }

        return back()->with('success', 'Reminder emails are being sent.');
    }



    public function showDoughnutCharts()
    {
        // Define an array of question_ids and their corresponding question numbers
        $questionIds = [
            82 => 1,
            83 => 2,
            84 => 3,
            85 => 4,
            86 => 5,
            87 => 6,
            88 => 7,
            89 => 8,
            90 => 9,
            91 => 10,
            92 => 11,
        ];

        // An array to store chart data
        $chartData = [];

        foreach ($questionIds as $questionId => $questionNumber) {
            // Retrieve data from the database for a specific question_id
            $data = DB::table('evaluation_answers')
                ->where('question_id', $questionId)
                ->select('answer', DB::raw('count(*) as count'))
                ->groupBy('answer')
                ->get();

            // Retrieve the corresponding question text
            $question = DB::table('participant_evaluation')
                ->where('id', $questionId)
                ->value('Question');

            // Add the data and question to the array
            $chartData[] = [
                'question_number' => $questionNumber,
                'question' => $question, // Add the question text to the chart data
                'data' => $data,
            ];
        }

        return view('doughnut-charts', compact('chartData'));
    }




    public function showEvaluationData()
    {
        if (!Schema::hasTable('evaluations') || !Schema::hasTable('evaluation_answers')) {
            return view('evaluation_data', ['evaluationData' => collect()]);
        }

        $evaluationData = DB::table('evaluations')
            ->join('evaluation_answers', 'evaluations.id', '=', 'evaluation_answers.evaluation_id')
            ->select('evaluations.name', 'evaluation_answers.question_id', 'evaluation_answers.answer')
            ->orderBy('evaluations.name')
            ->orderBy('evaluation_answers.question_id')
            ->get();

        return view('evaluation_data', compact('evaluationData'));
    }






    public function edit_options($id)
    {
        // Retrieve the question and its options based on the provided $id
        $question = DB::table('participant_evaluation')->where('id', $id)->first();
        $options = DB::table('options')->where('question_id', $id)->get();

        // Return the view for editing options with the question and options data
        return view('options', compact('question', 'options',));
    }

    public function edit_speakers($id)
    {
        // Retrieve the question and its speakers based on the provided $id
        $question = DB::table('participant_evaluation')->where('id', $id)->first();

        // Fetch all speaker names for the given question
        $speakers = DB::table('speakers')->where('question_id', $id)->pluck('name')->toArray();

        // Return the view for editing speakers with the question and speakers data
        return view('speakers', compact('question', 'speakers'));
    }


    public function update_options(Request $request, $id)
    {
        $request->validate([
            'options' => 'array',
        ]);

        // Update or insert options for the given question ID ($id)
        $questionId = $id;
        $options = $request->input('options', []);

        // Delete existing options and insert updated options into the options table
        DB::table('options')
            ->where('question_id', $questionId)
            ->delete();

        $optionValues = [];
        if (!is_null($options)) {
            foreach ($options as $optionGroup) {
                if (!is_null($optionGroup)) {
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
            }
        }

        // Sync CSV options column on participant_evaluation
        DB::table('participant_evaluation')->where('id', $questionId)->update([
            'options' => !empty($optionValues) ? implode(',', $optionValues) : null,
            'updated_at' => now(),
        ]);

        // Flash a success message to the session
        Session::flash('success_message', 'Options updated successfully');

        // Redirect back to the same view or a specific route
        return redirect()->back();
    }


    public function update_speakers(Request $request, $id)
    {
        $request->validate([
            'speakers' => 'required|array',
            'speakers.*' => 'string', // Adjust validation rules as needed
        ]);

        // Update or insert speakers for the given question ID ($id)
        $questionId = $id;
        $speakers = $request->input('speakers', []);

        // Delete existing speakers and insert updated speakers into the speakers table
        DB::table('speakers')
            ->where('question_id', $questionId)
            ->delete();

        $question = DB::table('participant_evaluation')->where('id', $questionId)->first();
        foreach ($speakers as $speakerName) {
            DB::table('speakers')->insert([
                'question_id' => $questionId,
                'event_id' => $question->event_id ?? null,
                'name' => $speakerName,
            ]);
        }

        // Flash a success message to the session
        Session::flash('success_message', 'Speakers updated successfully');

        // Redirect back to the same view or a specific route
        return redirect()->back();
    }



    public function viewEvaluation($evaluationId)
    {
        // Define the section order
        $sectionOrder = [
            'PRE-ARRIVAL',
            'ARRIVAL',
            'CONFERENCE LOCATION/FACILITIES',
            'CONFERENCE SESSIONS',
            'SPEAKERS',
            'ICAM & ACTIVITIES/FUNCTIONS',
        ];

        // Fetch data from the database based on the evaluation ID
        $evaluation = DB::table('evaluations')->find($evaluationId);
        $answers = DB::table('evaluation_answers')->where('evaluation_id', $evaluationId)->get();

        // Fetch questions, options, speakers, and other necessary data
        // You might need to update the following lines based on your actual table structure
        $questions = DB::table('participant_evaluation')->get();
        $options = DB::table('options')->get()->toArray();
        $speakers = DB::table('speakers')->get()->groupBy('question_id');

        $sections = $questions->pluck('Section')->unique();

        // Populate optionsByQuestion array
        $optionsByQuestion = [];

        foreach ($sections as $section) {
            $sectionQuestions = $questions->where('Section', $section);

            foreach ($sectionQuestions as $question) {
                $questionId = $question->id;

                $optionsForQuestion = array_filter($options, function ($option) use ($questionId) {
                    return $option->question_id === $questionId;
                });

                $optionsTexts = array_column($optionsForQuestion, 'value');
                $optionsByQuestion[$questionId] = $optionsTexts;
            }
        }

        return view('fileupload.view-evaluation', compact('evaluation', 'answers', 'questions', 'sections', 'optionsByQuestion', 'speakers', 'evaluationId','sectionOrder'));
    }




    public function downloadApk()
    {
        $file = public_path('apk/icam_final_dev.apk');

        $headers = [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="icam_final_dev.apk"',
        ];

        return response()->stream(
            function () use ($file) {
                readfile($file);
            },
            200,
            $headers
        )->headers->set('P3P', 'CP=IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT');
    }




    public function showGraph()
    {
        // Fetch evaluation data from the database for regular questions
        $regularQuestionsData = DB::table('evaluation_answers')
            ->select('question_id', 'answer', DB::raw('COUNT(*) as count'))
            ->groupBy('question_id', 'answer')
            ->get();

        // Fetch evaluation data from the database for speakers' ratings with speaker names
        $speakersRatingsData = DB::table('evaluation_to_speakers')
            ->join('speakers', 'evaluation_to_speakers.speaker_id', '=', 'speakers.id')
            ->select('speakers.speaker_name', 'evaluation_to_speakers.rating', DB::raw('COUNT(*) as count'))
            ->groupBy('speakers.speaker_name', 'evaluation_to_speakers.rating')
            ->get();

        // Extract unique speaker names
        $speakerNames = $speakersRatingsData->pluck('speaker_name')->unique()->values()->toArray();


        $valueToScore = [
            'Strongly Agree' => 5,
            'Excellent' => 5,
            'Poor' => 1,
            'Very poor' => 5,
            'Good' => 5,
            'Agree' => 4,
            'Neutral' => 3,
            'Disagree' => 2,
            'Strongly disagree' => 1,
            'Extremely valuable' => 5,
            'Very valuable' => 4,
            'Somewhat valuable' => 3,
            'Not so valuable' => 2,
            'Not at all valuable' => 1,
            'Exceeded expectations' => 5,
            'Met expectations' => 3,
            'Below expectations' =>1,
            'Very high quality' => 5,
            'High quality' => 4,
            'Neither high nor low quality' => 3,
            'Low quality' => 2,
            'Very low quality' => 1,
            'Very high variety' => 5,
            'High Variety' => 4,
            'Neither high nor low variety' => 3,
            'Low variety' => 2,
            'Very low variety' => 1,
            'A great deal' => 5,
            'A lot' => 4,
            'A moderate amount' => 3,
            'A little' => 2,
            'None at all' => 1,
            'Extremely useful' => 5,
            'Very useful' => 4,
            'Somewhat useful' => 3,
            'Not so useful' => 2,
            'Not at all useful' => 1,

            'Yes' => 5,'Maybe' => 3,'No' => 1,
        ];
        // ... (your existing $valueToScore mapping)

        // Organize data for the regular questions chart
        $regularQuestionsChartData = [];
        foreach ($regularQuestionsData as $data) {
            if (!isset($regularQuestionsChartData[$data->question_id])) {
                $regularQuestionsChartData[$data->question_id] = 0;
            }
            $regularQuestionsChartData[$data->question_id] += ($valueToScore[$data->answer] ?? 0) * $data->count;
        }

        // Organize data for the speakers' ratings chart with separate lines for each radio label
        $speakersRatingsChartData = [];

        $labels = ['Extremely valuable', 'Very valuable', 'Somewhat valuable', 'Not so valuable', 'Not at all valuable'];

        foreach ($labels as $label) {
            $speakersRatingsChartData[$label] = [];

            foreach ($speakersRatingsData as $data) {
                if (!isset($speakersRatingsChartData[$label][$data->speaker_name])) {
                    $speakersRatingsChartData[$label][$data->speaker_name] = 0;
                }

                if ($data->rating === $label) {
                    $speakersRatingsChartData[$label][$data->speaker_name] += $data->count;
                }
            }
        }


        return view('graph', compact('regularQuestionsChartData', 'speakersRatingsChartData', 'labels', 'speakerNames'));

    }




    public function index($event_id)
    {
        // Retrieve evaluations based on the event_id, if needed
        // For example, you might filter evaluations related to the event_id here
        $evaluations = DB::table('evaluations')
            ->select('id', 'name', 'email')
            ->where('event_id', $event_id) // Assuming you have an event_id column in evaluations
            ->get();

        $evaluationId = $evaluations->first()->id ?? null;

        // Pass the event_id to the view along with evaluations
        return view('fileupload.evaluation', ['evaluations' => $evaluations,'evaluationId' => $evaluationId, 'event_id' => $event_id]);
    }





    public function createEvaluationQuestions(Request $request)
    {
        $event_id = $request->id;
//        dd($event_id);
//        $event_name = DB::table('events')->where('event_id', ($request->id))->value('event_name');
        $events = Event::all();
        return view('Event_evaluation.index', compact('event_id', 'events'));
    }


    public function editEvaluationQuestions(Request $id)
    {
        $event_id = $id->id;
//        dd($event_id);
        try {
            $data = DB::table('participant_evaluation')
                ->select('questions as Question', 'section as Section', 'type as Type', 'event_id as Event_id', 'id')
                ->where('event_id', $event_id)
                ->get();

            if ($data->isEmpty()) {
//                throw new Exception("No content available");
            }
        } catch (Exception $exception) {
            return redirect()->back()->withErrors(["exception" => "{$exception->getMessage()}"]);
        }

        return view("Evaluate_Here.index", compact('data', 'event_id'));


    }



    public function edit_question($id)
    {
        // Retrieve the question, options, and speakers based on the provided $id
        $question = DB::table('participant_evaluation')->where('id', $id)->first();
        $options = DB::table('options')->where('question_id', $id)->get();
        $speakers = DB::table('speakers')->where('question_id', $id)->pluck('name')->toArray();

        // Return the view for editing the entire question with prefilled data
        return view('edit_question.index', compact('question', 'options', 'speakers',));
    }


    public function update_question(Request $request)
    {
        $request->validate([
            'Question' => 'required',
            'Section' => 'required',
            'Event_id' => 'required',
            'Type' => 'required',
        ]);

        // Extract the form data from the $request object
        $questionId = $request->input('id'); // Assuming 'id' is the name of the hidden input field containing the question ID
        $question = $request->input('Question');
        $section = $request->input('Section');
        $type = $request->input('Type');
        $event_id = $request->input('Event_id');
        $options = $request->input('options', []);
        $speakers = $request->input('speakers', []);

        // Update data in the participant_evaluations table
        DB::table('participant_evaluation')
            ->where('id', $questionId)
            ->update([
                'questions' => $question,
                'section' => $section,
                'type' => $type,
                'event_id' => $event_id,
            ]);

        // Delete existing options and insert updated options into the options table
        DB::table('options')
            ->where('question_id', $questionId)
            ->delete();
        $optionValues = [];
        if (!is_null($options)) {
            foreach ($options as $optionGroup) {
                if (!is_null($optionGroup)) {
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
            }
        }

        // Sync CSV options column on participant_evaluation
        DB::table('participant_evaluation')->where('id', $questionId)->update([
            'options' => !empty($optionValues) ? implode(',', $optionValues) : null,
            'updated_at' => now(),
        ]);

        // Delete existing speakers and insert updated speakers into the speakers table
        DB::table('speakers')
            ->where('question_id', $questionId)
            ->delete();

        foreach ($speakers as $speakerName) {
            DB::table('speakers')->insert([
                'question_id' => $questionId,
                'event_id' => $event_id,
                'name' => $speakerName,
            ]);
        }


        // Flash a success message to the session
        Session::flash('success_message', 'Data updated successfully');

        // Redirect back to the same view
        return redirect()->back();
    }



    public function delete_question($id, Request $request)
    {
        $event_id = $request->get('event_id');

        try {
            DB::table('participant_evaluation')->where('id', $id)->delete();
            return redirect()->route('evaluate-here', ['id' => $event_id])->with('message', 'Question deleted successfully');
        } catch (Exception $exception) {
            return redirect()->back()->withErrors(["exception" => "{$exception->getMessage()}"]);
        }
    }


//ADDEDDDD
    public function displayEvaluationForm()
    {
        $questions = DB::table('participant_evaluation')->get();

        return view('evaluation_form.index', [$questions], compact('questions'));
    }




    public function storeEvaluationData(Request $request, $reference_code, $event_id)
    {
        // Check if already submitted
        $existing = DB::table('evaluation_submissions')
            ->where('reference_code', $reference_code)
            ->where('event_id', $event_id)
            ->exists();

        if ($existing) {
            return redirect()->back()->with('error', 'You have already submitted an evaluation for this event.');
        }

        // Build answers payload
        $payload = [
            'answers' => $request->input('answers', []),
            'ratings' => $request->input('ratings', []),
            'text_answer' => $request->input('text_answer', []),
            'name' => $request->input('Name'),
            'email' => $request->input('Email'),
        ];

        DB::table('evaluation_submissions')->insert([
            'reference_code' => $reference_code,
            'event_id' => $event_id,
            'answers' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get event and participant for email
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $participant = DB::table('event_participants')
            ->where('reference_code', $reference_code)
            ->where('event_id', $event_id)
            ->first();

        $certUrl = route('download_certificate_pdf', [
            'reference_code' => $reference_code,
            'event_id' => $event_id,
        ]);

        // Send email with certificate link
        try {
            Mail::to($participant->email_address ?? '')->send(new \App\Mail\Evaluation([
                'name' => $participant->participant ?? 'Participant',
                'event_name' => $event->event_name ?? '',
                'certUrl' => $certUrl,
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Evaluation email failed: ' . $e->getMessage());
        }

        return redirect()->route('view_certificate', [
            'id1' => $reference_code,
            'id2' => $event_id,
        ])->with('success', 'Evaluation submitted successfully! Your certificate is ready.');
    }




    public function show_evaluation($id1, $id2)
    {
        $event_id = $id2;
        $reference_code = $id1;

        $questions = DB::table('participant_evaluation')
            ->where('event_id', $event_id)
            ->get();

        // Get participant info
        $participant = DB::table('event_participants')
            ->where('reference_code', $reference_code)
            ->where('event_id', $event_id)
            ->first();
        $participantName = $participant->participant ?? 'N/A';
        $participantEmail = $participant->email_address ?? 'N/A';

        // Build options array from comma-separated values
        $optionsByQuestion = [];
        foreach ($questions as $question) {
            if (!empty($question->options)) {
                $optionsByQuestion[$question->id] = array_map('trim', explode(',', $question->options));
            } else {
                $optionsByQuestion[$question->id] = [];
            }
        }

        // Get speakers for this event
        $speakers = DB::table('speakers')->where('event_id', $event_id)->get();

        // Get distinct sections
        $sections = $questions->pluck('section')->unique();

        return view('evaluation_form.index', compact(
            'reference_code', 'event_id', 'questions', 'sections',
            'optionsByQuestion', 'participantName', 'participantEmail', 'speakers'
        ));
    }



    public function submitForm(Request $request)
    {
        // Handle form submission
    }




    public function sendParticipantEmail($participant)
    {
        set_time_limit(300);
        Log::info('Before sending email'); // Log entry before email sending code

        if (isset($participant['Name']) && isset($participant['Reference_Code'])) {
            $data = [
                'Name' => 'Sarah Msosa',
                'Reference_Code' => 'icam3',
                'Event_Id' => '1',
//                'Name' => $participant['Name'],
//                'Reference_Code' => $participant['Reference_Code'],
//                'Event_Id' => $participant['Event_Id'],
                // Add other participant data as needed
            ];
            Mail::to("sarahleemsosa@gmail.com")->send(new Evaluation($data));
//            Mail::to($participant['Email'])->send(new Evaluation($data));
            Log::info('Email sent successfully'); // Log entry after email sending code
        } else {
            // Handle the case where the required keys are not present in the $participant array
            // Log an error or take appropriate action
        }
    }



}



@extends('layouts.app')
<style>

    .container{
        /* ... other content styles ... */

        align-items: center; /* Center content horizontally */
    }

    .card-body{
        color:black;

        /*!* ... other content styles ... *!*/
        /*display: flex;*/
        /*!*flex-direction: column;*!*/
        /*!*justify-content: center; !* Center content vertically *!*!*/
        /*align-items: center; !* Center content horizontally *!*/

    }
    .radio-buttons {
        display: flex; /* Display radio buttons horizontally */
        gap: 10px; /* Add spacing between radio buttons */
      /*background-color: red;*/
    }


    .radio-label {
        display: inline-block; /* Ensure labels are inline */
        /*background-color:burlywood;*/
    }
    .radio-label {
        display: inline-block;
        margin-right: 10px; /* Adjust spacing between radio buttons */
    }



</style>
 <!-- Assuming you have a layout, replace with your layout name -->

@section('content')
    <div class="card">

        <div class="card-header" style="text-align: center">

        <h2>View Submission for {{ $evaluation->name }}</h2>
        </div>

        <div class="card-body">
            <!-- Loop through sections and generate HTML -->
            @php
                $questionNumber = 1; // Initialize question numbering outside of the loop
            @endphp

            @foreach ($sectionOrder as $section)
                @php
                    $sectionQuestions = $questions->where('Section', $section);
                @endphp

                @if ($sectionQuestions->count() > 0)
                    <div class="section" style="margin-bottom: 20px; color: black">
                        <h5 style="color: black">{{ $section }}</h5>

                        @foreach ($sectionQuestions as $question)
                            <div class="question">
                                <label>{{ $questionNumber }}. {{ $question->Question }}</label><br>
                                @if ($question->Type === 'text')
                                    @php
                                        $textAnswer = $answers->where('question_id', $question->id)->first()->text_answer ?? 'None';
                                    @endphp
                                    <p>{{ $textAnswer }}</p>
                                @else
                                    <div class="radio-buttons">
                                        @php
                                            $answerForQuestion = $answers->where('question_id', $question->id)->first();
                                        @endphp
                                        @if ($answerForQuestion)
                                            @foreach ($optionsByQuestion[$question->id] as $option)
                                                <label class="radio-label" style="display: inline-block; margin-right: 10px;">
                                                    <input type="radio" value="{{ $option }}" disabled
                                                           @if ($answerForQuestion && $answerForQuestion->answer === $option)
                                                           checked
                                                            @endif
                                                    >
                                                    {{ $option }}
                                                </label><br>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif

                                @if ($section === 'SPEAKERS')
                                    @php
                                        $questionSpeakers = $speakers->get($question->id);
                                    @endphp
                                    @if ($questionSpeakers->count() > 0)
                                        <table class="speakers-table">
                                            <tr>
                                                <th style="color: black">Speaker</th>
                                                @foreach ($optionsByQuestion[$question->id] as $option)
                                                    <td style="width: 160px;color: black">{{ $option }}</td>
                                                @endforeach
                                            </tr>
                                            <tbody>
                                            @foreach ($questionSpeakers as $speaker)
                                                @php
                                                    $ratingForSpeaker = DB::table('evaluation_to_speakers')
                                                        ->where('evaluation_id', $evaluation->id)
                                                        ->where('question_id', $question->id)
                                                        ->where('speaker_id', $speaker->id)
                                                        ->value('rating');
                                                @endphp
                                                <tr>
                                                    <td style="width: 160px; color: black;">{{ $speaker->speaker_name }}</td>
                                                    @foreach ($optionsByQuestion[$question->id] as $option)
                                                        <td style="width: 160px;">
                                                            <input type="radio" value="{{ $option }}" disabled
                                                                   @if ($ratingForSpeaker && $ratingForSpeaker == $option)
                                                                   checked
                                                                    @endif
                                                            >
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p>No speakers listed.</p>
                                    @endif
                                @endif

                                @php
                                    $questionNumber++;
                                @endphp
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>

@endsection






{{--@extends('layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container">--}}
{{--        <h2>Evaluation Form - View Submission</h2>--}}
{{--        <form>--}}
{{--            <div class="field">--}}
{{--                <label>Name:</label>--}}
{{--                <input type="text" value="{{ $evaluation->name }}" readonly>--}}
{{--            </div>--}}

{{--            <div class="field">--}}
{{--                <label>Email:</label>--}}
{{--                <input type="text" value="{{ $evaluation->email }}" readonly>--}}
{{--            </div>--}}

{{--        @foreach ($sections as $section)--}}
{{--            {!! $sectionsHtml[$section] !!}--}}
{{--        @endforeach--}}

{{--        <!-- Display Name and Email fields again -->--}}
{{--            <div class="field">--}}
{{--                <label>Name:</label>--}}
{{--                <input type="text" value="{{ $evaluation->name }}" readonly>--}}
{{--            </div>--}}

{{--            <div class="field">--}}
{{--                <label>Email:</label>--}}
{{--                <input type="text" value="{{ $evaluation->email }}" readonly>--}}
{{--            </div>--}}

{{--            <!-- Add a back button to go back to the list of evaluations -->--}}
{{--            <a href="{{ route('fileupload_evaluation') }}" class="btn btn-primary">Back</a>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--@endsection--}}

{{--@extends('layouts.app')--}}

{{--@section('content')--}}
{{--    <div class="container">--}}
{{--        <h2>Evaluation Form - View Submission</h2>--}}
{{--        <form>--}}
{{--            <div class="field">--}}
{{--                <label>Name:</label>--}}
{{--                <input type="text" value="{{ $evaluation->name }}" readonly>--}}
{{--            </div>--}}

{{--            <div class="field">--}}
{{--                <label>Email:</label>--}}
{{--                <input type="text" value="{{ $evaluation->email }}" readonly>--}}
{{--            </div>--}}

{{--        @foreach ($sections as $section)--}}
{{--            {!! $sectionsHtml[$section] !!}--}}
{{--        @endforeach--}}

{{--        <!-- Display Name and Email fields again -->--}}
{{--            <div class="field">--}}
{{--                <label>Name:</label>--}}
{{--                <input type="text" value="{{ $evaluation->name }}" readonly>--}}
{{--            </div>--}}

{{--            <div class="field">--}}
{{--                <label>Email:</label>--}}
{{--                <input type="text" value="{{ $evaluation->email }}" readonly>--}}
{{--            </div>--}}

{{--            <!-- Add a back button to go back to the list of evaluations -->--}}
{{--            <a href="{{ route('fileupload_evaluation') }}" class="btn btn-primary">Back</a>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--@endsection--}}



<?php


namespace App\Helpers;

use Exception;
use App\Models\MealCoupon;
use App\Models\Participant;

class JMealsInterface
{
    public static function AppendParticipantsMealCoupons(array $participants): Exception|array
    {
        try {
            $participantsWithExtraMeals = [];
            $pageParticipantsExtraMeals = [];

            foreach ($participants as $key=> $participant) {
                $participant = (object) $participant;

                $participant->extraMeals = [];

                $meals = MealCoupon::where("participant_reference_code", $participant->reference_code)->get()->toArray();

                if(count($meals) > 1) {
                    $extraMealPages = $temp = [];
                    $processedMeals = [];
                    foreach ($meals as $index => $meal) {
                        $meal = (object) $meal;
                        if($meal->unique_code !== $participant->reference_code) {
                            $processedMeals[] = $meal;
                            if($index > 0 && $index %5 === 0) {
                                $extraMealPages[] = $temp;

                                # Add Blank Back Page
                                $extraMealPages[] = [];

                                # Add Participant Meals
                                $pageParticipantsExtraMeals[$participant->participant] = $extraMealPages;

                                # Reset Temporary file
                                $temp = [];
                            } else {
                                $temp[] = $meal;
                            }
                        }

                    }

                    $totalExtraMeals = count($processedMeals);
                    $totalPages =  ceil($totalExtraMeals/6);

                    $participant->extraMeals = $processedMeals;
//                if(count($extraMealPages) % 2 === )
                    $participant->extraMealPages = $extraMealPages;
                }

                if($key % 4 === 3) {
                    $participant->pageParticipantsExtraMeals = $pageParticipantsExtraMeals;
                    $pageParticipantsExtraMeals = [];
                }

                $participantsWithExtraMeals[] = $participant;

            }

            $participants =  $participantsWithExtraMeals;
        }
        catch (Exception $exception) {
            return new Exception("Failed to process Meal Coupons");
        }

//        dd($participants);
        return $participants;
    }

    public static function ProcessParticipantsMealCouponsBKP(array $participants) {
        try {
            $participantsWithExtraMeals = [];

            foreach ($participants as $key=> $participant) {
                $participant = (object) $participant;

                $participant->extraMeals = [];

                $meals = MealCoupon::where("participant_reference_code", $participant->reference_code)->get()->toArray();

                if(count($meals) > 1) {
                    $extraMealPages = $temp = [];
                    $processedMeals = [];
                    foreach ($meals as $index => $meal) {
                        $meal = (object) $meal;
                        if($meal->unique_code !== $participant->reference_code) {
                            $processedMeals[] = $meal;
                            if($index > 0 && $index %5 === 0) {
                                $extraMealPages[] = $temp;
                                $extraMealPages[] = [];
                                $temp = [];
                            } else {
                                $temp[] = $meal;
                            }
                        }

                    }

                    $totalExtraMeals = count($processedMeals);
                    $totalPages =  ceil($totalExtraMeals/6);

                    $participant->extraMeals = $processedMeals;
//                if(count($extraMealPages) % 2 === )
                    $participant->extraMealPages = $extraMealPages;
                }

                $participantsWithExtraMeals[] = $participant;
            }

            $participants =  $participantsWithExtraMeals;
        }
        catch (Exception $exception) {
            throw new Exception("Failed to process Meal Coupons");
        }

        return $participants;
    }
}

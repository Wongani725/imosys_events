<?php

namespace App\Http\Controllers;

use App\Models\MealCoupon;
use Exception;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\MealCouponPrintQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrintJobController extends Controller
{
    public function getPendingParticipants()
    {
        try {
            #$queue = MealCouponPrintQueue::where('status', 'pending')->get();
//        $subSelect = DB::table("meal_coupon as mp")
//            ->selectRaw("JSON_ARRAYAGG(JSON_OBJECT('attendant', name, 'phone', phone)) as meal_coupons")
//            ->join(
//                "users as u", "u.id",
//                "mp.printed_by"
//            )
//            ->where('meal_coupon.status', '!=', 'used')
//            ->whereColumn("mp.participant_reference_code", "i_meal_coupons_print_queue.participant_reference");
//        $queue = MealCouponPrintQueue::selectSub($subSelect)
            $queues = MealCouponPrintQueue::where('i_meal_coupons_print_queue.status', '!=', 'printed')->get();


            if(empty($queues->toArray())) {
                throw new Exception("Print queue empty");
            }

            $enhancedQueue = [];
            foreach ($queues as $queue) {
                $mealCoupons =  DB::table("meal_coupon as mp")
                    ->join("users as u", "u.id","mp.printed_by")
                    ->where([
                        ["participant_reference_code", $queue->participant_reference],
                        ["mp.status", "pending"],
                    ])
                    ->get(["mp.*", "u.name as attendant"]);
//                $queue['meal_coupons'] = $mealCoupons;
                if(!empty($mealCoupons->toArray())) {
                    $queue->meal_coupons = $mealCoupons;
                    $enhancedQueue[] = $queue;
                }
            }

            $queue = $enhancedQueue;
        }
        catch (Exception $exception) {
            return Helper::APIResponse(0, "{$exception->getMessage()}",HTTP_FAILED);
        }

        $data = compact('queue');
        return Helper::APIResponse(1, "Request completed",HTTP_SUCCESS, $data);
    }

    public function updateMealCouponStatus(Request $request) {
        $rules = [
            'meal_coupon_code' => 'required|string|exists:meal_coupon,unique_code',
            'status' => 'required|string|in:printed,pending',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "$error",HTTP_UNPROCESSABLE);
        }

        DB::beginTransaction();
        try {
            $mealCoupon = MealCoupon::where("unique_code", "{$request->meal_coupon_code}")->first();

            switch($mealCoupon->status) {
                case "pending": break;
                case "used":
                    throw new Exception("Meal Coupon already used on {$mealCoupon->date_used}");
                case "printed":
                default:
                    throw new Exception("Meal Coupon already printed on {$mealCoupon->date_printed}");
            }

            $mealCoupon->status = "{$request->status}";
            $mealCoupon->date_printed = Helper::Now();
            $mealCoupon->save();
            $mealCoupon->refresh();

            DB::commit();
        }
        catch (Exception $exception) {
            DB::rollBack();
            return Helper::APIResponse(0, "{$exception->getMessage()}",HTTP_FAILED);
        }

        return Helper::APIResponse(1, "Meal coupon updated successfully",HTTP_SUCCESS);
    }



    public function updateStatus(Request $request)
    {
        $participantId = $request->input('id');

        $participant = MealCouponPrintQueue::find($participantId);

        if (!$participant) {
            return response()->json(['message' => 'Participant not found.'], 404);
        }

        $participant->status = 'printed';
        $participant->save();

//        return response()->json([
//            'message' => 'Participant status updated successfully.',
//            'participant' => $participant
//        ]);

        $data = compact('participant');
        return Helper::APIResponse(1, "Queue status updated successfully.",HTTP_SUCCESS, $data);

    }

}

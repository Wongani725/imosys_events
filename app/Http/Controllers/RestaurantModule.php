<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Participant;
use App\Models\RestaurantModule;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class RestaurantController extends Controller
{
    public function generateMealCoupons()
    {
        Artisan::call('meal_coupons:generate');

        $output = Artisan::output();

        return response()->json([
            'message' => 'Meal coupons generated successfully!',
            'output' => $output,
        ]);
    }

    public function handle()
    {
        $participants = Participant::where('balance', 0)->get();

        foreach ($participants as $participant) {
            $mealCoupons = [];

            for ($i = 1; $i <= 5; $i++) {
                // Generate a unique filename for each meal coupon
                $filename = $participant->reference_code . '_coupon_' . $i . '.jpg';

                // Save the meal coupon image to the storage folder
                $mealCouponPath = Storage::disk('public')->path('meal_coupons/' . $filename);

                // Generate the meal coupon image
                $imageManager = new ImageManager();
                $mealCouponImage = $imageManager->canvas(500, 500, '#ffffff');
                $mealCouponImage->text('Meal Coupon', 250, 250, function ($font) {
                    $font->file(public_path('fonts/arial.ttf'));
                    $font->size(48);
                    $font->color('#000000');
                    $font->align('center');
                    $font->valign('middle');
                });

                // Save the meal coupon image
                $mealCouponImage->save($mealCouponPath);

                // Store the filename in the mealCoupons array
                $mealCoupons[] = $filename;
            }

            // Create or update the participant's record in the restaurant_module table
            RestaurantModule::updateOrCreate(
                ['reference_code' => $participant->reference_code],
                ['total_meals' => 5, 'meal_coupons' => json_encode($mealCoupons)]
            );
        }

        $this->info('Meal coupons generated successfully!');
    }
}

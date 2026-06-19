<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\RestaurantMealCoupon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import the QR code facade

class RestaurantMeals extends Controller
{
    public function generateMealCoupons()
    {
        $participants = Participant::where('balance', 0)->get();

        foreach ($participants as $participant) {
            $mealCoupons = [];

            for ($i = 1; $i <= 5; $i++) {
// Generate a unique filename for each meal coupon
                $filename = $participant->reference_code . '_coupon_' . $i . '.jpg';

// Save the meal coupon image to the public folder
                $mealCouponPath = public_path('meal_coupons/' . $filename);

// Generate the meal coupon image
                $mealCouponImage = Image::canvas(500, 500, '#ffffff');
                $mealCouponImage->text('Meal Coupon', 250, 250, function ($font) {
                    $font->size(48);
                    $font->color('#000000');
                    $font->align('center');
                    $font->valign('middle');
                });

// Generate the unique QR code for the meal coupon
                $qrCodeData = QrCode::encoding('UTF-8')->size(250)->generate($filename);

// Insert the QR code onto the meal coupon image
                $mealCouponImage->text($qrCodeData, 250, 300, function ($font) {
                    $font->size(24);
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
            RestaurantMealCoupon::updateOrCreate(
                ['reference_code' => $participant->reference_code],
                ['total_meals' => 5, 'meal_coupons' => json_encode($mealCoupons)]
            );
        }

        return response()->json([
            'message' => 'Meal coupons generated successfully!',
        ]);
    }
}

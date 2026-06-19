<?php
namespace App\Http\Controllers;

use App\Models\Participant;

use App\Models\RestaurantModule;
use Illuminate\Http\Request;

class Restaurant extends Controller
{
public function scan-meal-coupon(Request $request)
{
// Validate the request
$request->validate([
'reference_code' => 'required|string'
]);

// Get the reference code from the request
$referenceCode = $request->input('reference_code');

// Check if the reference code exists in the event_participants table
$participant = Participant::where('reference_code', $referenceCode)->first();

if ($participant) {
// Reference code exists
// Check if the reference code already exists in the restaurant table
$existingRestaurant = RestaurantModule::where('reference_code', $referenceCode)->first();

if ($existingRestaurant) {
// Reference code already exists in the restaurant table
return response()->json(['message' => 'Reference code already used.'], 200);
}

// Update the status in the restaurant_module table
$restaurantModule = RestaurantModule::where('reference_code', $referenceCode)->first();

if ($restaurantModule) {
$restaurantModule->status = 'used';
$restaurantModule->save();
}

// Insert a new record into the restaurant table
$restaurant = new RestaurantModule();
$restaurant->reference_code = $referenceCode;
$restaurant->status = 'used';
$restaurant->save();

return response()->json(['message' => 'Reference code is available. Status changed to Used and inserted in the restaurant table.'], 200);
} else {
// Reference code does not exist in the event_participants table
return response()->json(['message' => 'Reference code is not available.'], 200);
}
}
}

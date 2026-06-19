<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Add this line to import the DB facade



class AvatarController extends Controller
{

    public function upload(Request $request)
    {
        $referenceCode = $request->input('reference_code');
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:9048',
        ]);

        // Retrieve the current upload count for the participant
        $uploadCount = DB::table('event_participants')
            ->where('reference_code', $referenceCode)
            ->value('upload_count');

        // Calculate the remaining upload count
        $remainingUploads = 2 - $uploadCount;

        if ($uploadCount >= 3) {
            // Inform the user that further uploads are not allowed
            return redirect()->back()->with('error', 'You have already uploaded 3 times. Further uploads are not allowed.');
        }

        if ($request->file('image')->isValid()) {
            try {
                $file = $request->image;
                $imageName = $referenceCode . '.jpg'; // Set the file extension to .jpg
                $imagesfolder = 'avatars';
                $destinationPath = public_path($imagesfolder);
                $upload = $file->move($destinationPath, $imageName);

                // Increment the upload count for the participant in the "event_participants" table
                DB::table('event_participants')
                    ->where('reference_code', $referenceCode)
                    ->increment('upload_count');

                // Inform the user about the remaining upload count
                if ($remainingUploads > 0) {
                    return redirect()->back()->with('success', 'Image uploaded successfully. You have ' . $remainingUploads . ' more upload(s) to go.');
                } else {
                    return redirect()->back()->with('success', 'Image uploaded successfully. You have reached the maximum upload limit.');
                }
            } catch (Exception $e) {
                // Handle any errors that may occur during the upload process
                return redirect()->back()->with('error', 'An error occurred while uploading the image.');
            }
        } else {
            // Inform the user about the image upload validation failure
            return redirect()->back()->with('error', 'Invalid image format or size. Please upload a valid image (JPEG, PNG, JPG, GIF) with a maximum size of 9048 KB.');
        }
    }



//    public function upload(Request $request)
//    {
//        $referenceCode = $request->input('reference_code');
//        $request->validate([
//            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:9048',
//        ]);
//        if ($request->file('image')->isValid()) {
//            //dd($request->all());
//            try {
//                $file = $request->image;
//                //$imageName = $referenceCode . '.' . $request->image->getClientOriginalExtension();
//                $imageName = $referenceCode . '.jpg'; // Set the file extension to .jpg
//                $imagesfolder='avatars';
//                $destinationPath = public_path( $imagesfolder);
//                $upload=$file->move($destinationPath,$imageName);
//            }catch (Exception $e){
//                //return redirect()->back()->with('error', 'Error uploading image.');
//            }
//        }
//
//
//
////        // Check if the user has exceeded the avatar change limit
////        $avatarChangeCount = $request->session()->get('avatarChangeCount', 0);
////        if ($avatarChangeCount >= 1) {
////            return redirect()->back()->withErrors(['message' => 'You have reached the maximum limit for changing your avatar.']);
////        }
////
////        // Process avatar upload logic here
////
////        // Increment the avatar change count
////        $request->session()->put('avatarChangeCount', $avatarChangeCount + 1);
////
////        // Redirect back with success message
////        return redirect()->back()->with('success', 'Avatar changed successfully.');
//
//
//    }

//    public function upload(Request $request)
//    {
//        $referenceCode = $request->input('reference_code');
//        $request->validate([
//            'images' => 'required|array|max:3', // Make sure it is an array of images, with maximum of 3 images
//            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:9048', // Validate individual images
//        ]);
//
//        // Retrieve the uploaded images
//        $uploadedImages = $request->file('images');
//
//        foreach ($uploadedImages as $file) {
//            if ($file->isValid()) {
//                try {
//                    $imageName = $referenceCode . '_' . time() . '.' . $file->getClientOriginalExtension(); // Add timestamp to prevent filename collisions
//                    $imagesfolder = 'avatars';
//                    $destinationPath = public_path($imagesfolder);
//                    $upload = $file->move($destinationPath, $imageName);
//                } catch (Exception $e) {
//                    // Handle any exceptions or errors that may occur during file upload
//                    //return redirect()->back()->with('error', 'Error uploading image.');
//                }
//            }
//        }
//
//        // Handle successful upload or any other logic
//
//        // Redirect back with a success message if needed
//        return redirect()->back()->with('success', 'Images uploaded successfully!');
//    }

//    public function upload(Request $request)
//    {
//        $referenceCode = $request->input('reference_code');
//        $request->validate([
//            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:9048',
//        ]);
//        if ($request->file('image')->isValid()) {
//            //dd($request->all());
//            try {
//                $file = $request->image;
//                //$imageName = $referenceCode . '.' . $request->image->getClientOriginalExtension();
//                $imageName = $referenceCode . '.jpg'; // Set the file extension to .jpg
//                $imagesfolder='avatars';
//                $destinationPath = public_path( $imagesfolder);
//                $upload=$file->move($destinationPath,$imageName);
//            }catch (Exception $e){
//                //return redirect()->back()->with('error', 'Error uploading image.');
//            }
//        }
//
//
//
////        // Check if the user has exceeded the avatar change limit
////        $avatarChangeCount = $request->session()->get('avatarChangeCount', 0);
////        if ($avatarChangeCount >= 3) {
////            return redirect()->back()->withErrors(['message' => 'You have reached the maximum limit for changing your avatar.']);
////        }
////
////        // Process avatar upload logic here
////
////        // Increment the avatar change count
////        $request->session()->put('avatarChangeCount', $avatarChangeCount + 1);
////
////        // Redirect back with success message
////        return redirect()->back()->with('success', 'Avatar changed successfully.');
//
//        return back()->with('message', 'Image Updated Successfully');
//    }

    public function uploadEventlocationImage(Request $request)
    {
        $event_id = $request->input('event_id');
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:9048',
        ]);
        if ($request->file('image')->isValid()) {
            //dd($request->all());
            try {
                $file = $request->image;
                //$imageName = $referenceCode . '.' . $request->image->getClientOriginalExtension();
                $imageName = $event_id . '.jpg'; // Set the file extension to .jpg
                $imagesfolder='avatars';
                $destinationPath = public_path( $imagesfolder);
                $upload=$file->move($destinationPath,$imageName);
            }catch (Exception $e){
                //return redirect()->back()->with('error', 'Error uploading image.');
            }
        }
        //return redirect()->back()->with('error', 'Error uploading image.');
        return back()->with('message', 'Image Updated Successfully');
    }
}

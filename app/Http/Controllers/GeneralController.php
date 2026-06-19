<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\Helper;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GeneralController extends Controller
{
    public function ajaxFileUpload(Request $request) {
        return Response::json("failed", 400);
        $input = $request->all();
        $rules = array(
            'file' => 'image|max:3000',
        );

        $validation = Validator::make($input, $rules);

        if ($validation->fails())
        {
            return Response::make($validation->errors->first(), 400);
        }

        try {
            $file = $request->file;
            $destinationPath = public_path().'/uploads';
            // If the uploads fail due to file system, you can try doing public_path().'/uploads'
            $fileName = '_' . time() . '.'. $file->extension();
            $type = $file->getClientMimeType();
            $size = $file->getSize();
            $destinationPath = public_path('uploads');

            $upload_success = $file->move($destinationPath, $fileName);

            if(!$upload_success ) {
                throw new Exception("failed to save file");
            }
        }
        catch (Exception $exception) {
            return Response::json("{$exception->getMessage()}", 400);
        }

        $file_path = "{$destinationPath}/{$filename}.{$extension}";
        return Response::json("{$file_path}", 200);
    }

    public function districtsAPI(Request $request) {
        $districts = District::all()->toArray();
        return Helper::APIResponse(1, "Districts Retrieved", $districts);
    }

    public function aboutDataAPI(Request $request) {
        $getting_started_message = "Alert button that  alerts your  emergency contacts to your precise location when you need assistance";
        $about_platform = "Alert button that  alerts your  emergency contacts to your precise location when you need assistance";
        $contact_information = [
            'emails'=>'info@alonda.mw, support@alonda.mw',
            'phones'=>'+265999228264, +265888313788'
        ];

        $host_organization = "iMoSyS";
        $sponsor_label = "Powered by iMoSyS";
        $terms_and_conditions = "we are not bearable of any consequences caused by using our platform";
        $disclaimer = "we are not bearable of any consequences caused by using our platform";

        $data = compact(
            'getting_started_message', 'about_platform', 'contact_information',
            'host_organization', 'sponsor_label', 'terms_and_conditions', 'disclaimer'
        );

        return Helper::APIResponse(1, 'Platform info retrieved', $data);
    }
}

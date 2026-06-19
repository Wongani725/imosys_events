<?php


namespace App\Http\Controllers;


use App\Helpers\UserSession;
use App\Models\ServiceProvider;
use Exception;
use App\Helpers\Helper;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request) {
        if($request->ajax()) {
            $users = User::whereNotNull("name")->whereNotNull("phone")->orderBy("id", "DESC")->get()->toArray();

            $totalUsers = count($users);

            return Helper::DataTableResponse($users, $totalUsers, $totalUsers, isset($request->draw) ? $request->draw : '');
        }

        $services = Service::where("parent", "!=", "other")->get()->toArray();
        $services = json_encode($services);

        $data = compact("services");

        return view('users.index', $data);
    }

    public function addUserAsServiceProvider(Request $request) {
        try {
            $user = User::where("unique_code", $request->reference)->first();

            if(empty($user)) {
                throw new Exception("User record not found");
            }
//            $services = Service::where("parent", "!=", "other")->get();
            $services = Service::whereNotIN("slug",
                DB::table("aa_services")
                    ->select("parent")
                    ->where("parent", "!=", "none")
                    ->where("parent", "!=", "other")
            )->where("is_limited", 0)->where("is_externally_added", 0)->get();
            $formTitle = "Form for making {$user->name} as service provider";
        }
        catch (Exception $exception) {
            return redirect()->back()->withErrors(["exception"=>$exception->getMessage()]);
        }

        $data = compact("formTitle", "services", "user");
        return view("users.add_as_service_provider", $data);
    }

    public function saveUserAsServiceProvider(Request $request) {
        $rules = [
            'business_name' => 'required|string',
            'avatar' => 'required|image',
            'business_type' => 'required|string|exists:aa_services,slug',
            'operating_areas' => 'required|string',
            'phone' => 'required|string|min:10|max:255',
            'reference' => 'required|string|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = Helper::FirstValidationError($validator->errors()->toArray());
            return redirect()->back()->withInput()->withErrors(["exception"=>$error]);
        }

        DB::beginTransaction();
        try {
            $user = User::where("unique_code", $request->reference)->first();
            $service = Service::where("slug",$request->business_type)->first();

            //Upload Avatar

            $file = $request->avatar;
            $fileName = $user->unique_code . '_' . time() . '.'. $file->extension();

            $type = $file->getClientMimeType();
            $size = $file->getSize();
            $individualProvidersDirectory = 'images/service_providers/individuals';
            $destinationPath = public_path($individualProvidersDirectory);

            $upload_success = $file->move($destinationPath, $fileName);

            if(!$upload_success ) {
                throw new Exception("failed to save avatar");
            }

            $baseURL = "https://web.alonda.mw";
            $avatarURL = "{$baseURL}/{$individualProvidersDirectory}/{$fileName}";
            $lastRankedServiceProvider = ServiceProvider::where("category", $service->slug)->orderBy("rank", "DESC")->first();
            $serviceProviderData = [
                "name"=> trim($request->business_name),
                "category"=> $service->slug,
                "code"=> $user->unique_code,
                "contact_phone"=> trim($request->phone),
                "logo"=> $avatarURL,
                "rank"=> !empty($lastRankedServiceProvider) ? (int)$lastRankedServiceProvider->rank + 1 : 1,
                "type"=> "individual",
                "operating_bases"=> trim($request->operating_areas),
                "status"=> ACTIVE,
                "created_by"=> UserSession::ID(),
            ];

            if(!empty($request->email)) {
                $serviceProviderData["contact_email"] = trim($request->email);
            }

            $serviceProvider = ServiceProvider::create($serviceProviderData);

            $serviceProvider->users()->attach($user);
            DB::commit();
        }
        catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(["exception"=> "{$exception->getMessage()}"]);
        }

        return redirect(route("service_provider_index"))->with("message", "{$user->name} is now added as a  {$service->name} provider");
    }
}
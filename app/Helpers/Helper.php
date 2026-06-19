<?php


namespace App\Helpers;

use DateTime;
use Exception;
use DatePeriod;
use DateInterval;
use SimpleXMLElement;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Helper
{
    public static function DateGreaterThanDate($date1, $date2) {
        $timestamp1 = strtotime($date1);
        $timestamp2 = strtotime($date2);
        if ($timestamp1 > $timestamp2){
            return true;
        }

        return false;
    }
    
    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function GreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $algorithm="Vincenty", $earthRadius = 6371000)
    {
        switch (strtolower($algorithm)) {
            case "haversine":
                // convert from degrees to radians
                $latFrom = deg2rad($latitudeFrom);
                $lonFrom = deg2rad($longitudeFrom);
                $latTo = deg2rad($latitudeTo);
                $lonTo = deg2rad($longitudeTo);

                $latDelta = $latTo - $latFrom;
                $lonDelta = $lonTo - $lonFrom;

                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                return $angle * $earthRadius;
            case "vincenty":default:
            // convert from degrees to radians
            $latFrom = deg2rad($latitudeFrom);
            $lonFrom = deg2rad($longitudeFrom);
            $latTo = deg2rad($latitudeTo);
            $lonTo = deg2rad($longitudeTo);

            $lonDelta = $lonTo - $lonFrom;
            $a = pow(cos($latTo) * sin($lonDelta), 2) +
                pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
            $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

            $angle = atan2(sqrt($a), $b);
            return $angle * $earthRadius;
        }
    }

    public static function DaysBetween(string $former_date, string $later_date): int
    {
        $datetime1 = new DateTime($former_date);

        $datetime2 = new DateTime($later_date);

        $difference = $datetime1->diff($datetime2);

        return $difference->d;
    }

    public static function MonthsBetween(string $former_date, string $later_date): int
    {
        $datetime1 = new DateTime($former_date);

        $datetime2 = new DateTime($later_date);

        $difference = $datetime1->diff($datetime2);

        return $difference->m;
    }

    public static function YearsBetween(string $former_date, string $later_date): int
    {
        $datetime1 = new DateTime($former_date);

        $datetime2 = new DateTime($later_date);

        $difference = $datetime1->diff($datetime2);

        return $difference->y;
    }

    public function ExtractDayFromDate(string $date, $format="Y-m-d"): string
    {
        $date = DateTime::createFromFormat($format, $date);
        return $date->format("d");
    }

    public static function PeriodBetweenTwoDates($start_date, $end_date, $format="Y-m-d") {
        $start_date = \DateTime::createFromFormat($format, $start_date);
        $end_date = \DateTime::createFromFormat($format, $end_date);

        return new \DatePeriod(
            $start_date,
            new \DateInterval('P1D'),
            $end_date->modify('+1 day')
        );
    }

    public static function DateListBetweenTwoDates($start_date, $end_date, $format="Y-m-d", $return_date_format="d") {
        if(empty($format)) {
            $format="Y-m-d";
        }

        $datePeriod = self::PeriodBetweenTwoDates($start_date, $end_date, $format);
        $daysList = [];
        foreach($datePeriod as $date) {
            $daysList[] = $date->format($return_date_format);
        }

        return $daysList;
    }

    public static function FirstDateOfCurrentMonth() {
        return date('Y-m-01');
    }

    public static function LastDateOfCurrentMonth() {
        return date('Y-m-t');
    }

    public static function AddDaysToDate($date, $days) {
        return date('Y-m-d', strtotime($date. "+ {$days} days"));
    }

    public static function SubtractDaysFromDate($date, $days) {
        return date('Y-m-d', strtotime($date. "- {$days} days"));
    }

    public static function DateInterval($new_date, $old_date, $abs=false) {
        $datetime1 = new DateTime($new_date);
        $datetime2 = new DateTime($old_date);
        $interval = $datetime1->diff($datetime2);
        $days = $interval->format('%a');
        return $abs ? $days : $interval->format("%r%a");
    }

    public static function DateIsNotAfterToday($date) {
        $today = date("Y-m-d"); //Today

        return strtotime($today) >= strtotime($date);
    }

    public static function Timestamp() {
        return date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s')));
    }

    public static function DateTime() {
        return date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s')));
    }

    public static function ConvertDateToMYSQLTimestamp($date_string) {
        return date("Y-m-d H:i:s",strtotime(str_replace('-', '/',$date_string)));
    }

    public static function CurrentTime($format="H:i:s") {
        return date($format);
    }

    public static function AddSecondsToCurrentTime($seconds, $format="H:i:s") {
        return date($format, strtotime("+{$seconds} seconds")) ;
    }

    public static function OrdinalSuffix( $n , $concatenate=true, $postfix=" Month"): string
    {
        $result = date('S',mktime(1,1,1,1,( (($n>=10)+($n>=20)+($n==0))*10 + $n%10) ));
        return $concatenate ? $n . $result . $postfix : $result . $postfix;
    }

    public static function IsIframeDisabled($src){
        try{
            $headers = get_headers($src, 1);
            $headers = array_change_key_case($headers, CASE_LOWER);
            // Check Content-Security-Policy
            if(isset($headers[strtolower('Content-Security-Policy')])){
                return true;
            }
            // Check X-Frame-Options
            if(isset($headers[strtolower('X-Frame-Options')]) &&
                (strtoupper($headers['X-Frame-Options']) == 'DENY' ||
                    strtoupper($headers['X-Frame-Options']) == 'SAMEORIGIN')
            ){
                return true;
            }
        } catch (Exception $ex) {
            // Ignore error
        }
        return false;
    }

    public static function SimpleHTMLDocument($body, $title="Simple HTML Document", $csrf=""): string
    {
        $document =  sprintf('
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <title>%1$s</title>
        <meta name="csrf-token" content="%2$s">
    </head>
    <body>
    <?php if(\Session::get("error");): ?>
    <div style="background-color: #d98585; color: darkred; width: 100%; min-height: 30px;">
        <?=\Session::get("error");?>
    </div>
    <?php endif;?>
    %3$s
    </body>
</html>
        ', $title,  $csrf, $body);

        return $document;
    }

    public static function Prefix($var, $prefix_value="0", $count=4) {
        return str_pad("{$var}", $count, "{$prefix_value}", STR_PAD_LEFT);
    }

    public static function DataTableResponse(array $data, $total, $filtered, $draw="", array  $extras=[]) {
        $extras['data'] = $data;
        $extras['recordsFiltered'] = $filtered;
        $extras['recordsTotal'] = $total;
        $extras['draw'] = $draw;

        return response()->json($extras);
    }

    public static function Jsonify($json_string, $associative = false, $depth = 512, $flags = 0) {
        return json_decode($json_string, $associative, $depth, $flags);
    }

    public static function Stringfy($json_string, $flags = 0, $depth = 512) {
        return json_encode($json_string, $flags, $depth);
    }

    public static function ConvertObjectListToArray($object_list) {
        $result = array_map(function ($value) {
            return (array)$value;
        }, $object_list);
        return $result;
    }


    public static function SetSessionValue($key, $values) {
        // dd("setting session");
        if(\session()->has($key)) {
            $session_data = \session($key);
            if(is_array($session_data)) {
                foreach ($values as $idx => $value) {
                    $session_data[$idx] = $value;
                }
            } else {
                $session_data = $values;
            }

            \session()->put($key, $session_data);
        } else {
            \session()->put($key, $values);
        }
    }

    public static function UID($prefix = "", $more_entropy = false): string
    {
        return uniqid($prefix, $more_entropy);
    }

    public static function GenerateUniqueNumber($min = 0, $max = null, $table=null, $column=null): int
    {
        $number = mt_rand($min, $max);

        // call the same function if the barcode exists already
        if(!empty($table) && !empty($column)){

            if (!self::IsUnique($number, $table, $column)) {
                return self::GenerateUniqueNumber($min, $max, $table, $column);
            }
        }

        return $number;
    }

    public static function IsUnique($value, $table, $column): bool
    {
        $rules = [$column=> "unique:{$table}"];
        $data = [$column=> $value];
        $validator = Validator::make($data, $rules);

        return !$validator->fails();
    }

    public static function ContainsWord($str, $word): bool
    {
        return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $str);
    }

    public static function QRCodeToken(int $size=250, $hmtl_entities = false) {
        $str = self::uniq_id();
        $hash = sha1($str);
        $token = $hash;
        $hash = sha1(self::uniq_id());
        $token .= $hash;
        $hash = sha1(self::uniq_id());
        $token .= $hash;
        $hash = sha1(self::uniq_id());
        $token .= $hash;

        $qrCode =  QrCode::size($size)
            ->style('round')
            ->color(123,31,163)
            ->eyeColor(0, 81, 45, 168, 81, 45, 168)
            ->eyeColor(1, 81, 45, 168, 81, 45, 168)
            ->eyeColor(2, 81, 45, 168, 81, 45, 168)
            ->generate($token);

        return ['token' => $token, 'qr'=> $hmtl_entities ? htmlentities($qrCode) : $qrCode];
    }

    public static function Post($base_url, $uri, $data=[], $timeout=500, $content_type= 'application/json') {
        try {
            $client = new Client(['base_uri' => "{$base_url}", 'timeout'  => $timeout]);
            $params['headers'] = ['Content-Type' => $content_type];

            $params['json'] = $data;

            $response = $client->post("{$uri}",$params);
            $body = $response->getBody();
        }
        catch (Exception $e) {
            return self::APIResponse(0, "request failed {$e->getMessage()}", HTTP_FAILED);
        }


        return json_decode($body);
    }

    public static function Get($base_url, $uri, $data=[], $timeout=20.0, $content_type= 'application/json') {
//        $client = new Client([
//            'base_uri' => 'http://www.google.com',
//        ]);
//
//        $response = $client->request('GET', 'search', [
//            'query' => ['q' => 'curl']
//        ]);
//        echo $response->getBody();

        $client = new Client(['base_uri' => "{$base_url}", 'timeout'  => $timeout]);
        $params['headers'] = ['Content-Type' => $content_type];

        $params['json'] = $data;

        $response = $client->Get("{$uri}",$params);

        return json_decode( $response->getBody()->getContents(), true);
    }

    public static function APIResponse( $status, $message, $code=200, array $data=[]) {
        if (is_array($code))
        {
            $data =  $code;
            $code = 201;
        }
        return response()->json(['status'=>$status, 'data' => $data, 'msg' => $message], $code);
    }

    public static function PushFirebaseNotification($server_key, array $recipients, $title, $message, $data= [], $sound="default") {
        $data = [
            "registration_ids"=> $recipients,
            "notification"=> [
                "title"=> $title,
                "body"=> $message,
                "sound"=> $sound,
                "extra"=> $data,
            ],
        ];

        $jsonData = json_encode($data);
        //return $jsonData;

        $headers = [
            'Authorization: key='. $server_key,
            'Content-Type: application/json',
        ];

        $handle = curl_init();

        curl_setopt($handle, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($handle, CURLOPT_POST, true);

        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);

        return curl_exec($handle);
    }

    public static function FirstValidationError(array $errors) {
        $validation_error = "";
        foreach ($errors as $error) {
            $validation_error = $error[0];
            break;
        }

        return $validation_error;
    }

    public static function InternationNumber($phone, $country_code="mw") {
        $code = "+265";
        switch (strtolower($country_code)) {
            case "mw":
            default:
                $code = "+265";
                break;
        }

        $phone[0] = (int) $phone[0] === 0 ? "" : $phone[0];
        if((int) $phone[0] === 0) {
            $phone = str_replace($phone, "{$code}", "0", 1);
        }

    }

    public function ExceptionMessage($prefix, Exception $e, $suffix="", $debug=true) {
        if(is_bool($suffix)) {
            $debug = $suffix;
            $suffix = "";
        }

        if($debug) {
            return "{$prefix} {$e->getMessage()} {$suffix}";
        }

        return "{$prefix}  {$suffix}";
    }

    public static function LaravelException(Exception $e, $prefix,  $suffix="") {
        return self::ExceptionMessage($prefix, $suffix, env("APP_DEBUG"));
    }

    public static function IsAPIRequest($request=null, $framework="laravel") {
        if($request === null)
        {
            switch (strtolower($framework)) {
                case 'laravel':
                default:
                    return \request()->is("*api*");
            }

        }

        switch (strtolower($framework)) {
            case 'laravel':
            default:
                return $request->is("*api*");
        }
    }

    public static function ActiveMenu($target, $classes="active"): string
    {
        if(filter_var($target, FILTER_VALIDATE_URL))
        {
            $target = ltrim(parse_url($target)["path"], "/");
        }
        return (request()->is("*{$target}/*") || request()->is("*{$target}*") || request()->is("*/{$target}*")) ? "{$classes}" : "";
    }

    public static function IsInputChecked($value, $true=true, $false=false) {
        if(!empty($value) && $value === "on") {
            return $true;
        }
        else {
            return $false;
        }
    }

    public static function Today(){
        return date("Y-m-d");
    }

    public static function Now(){
        return date("Y-m-d H:s:i");
    }

    public static function PostRequest(Request $request) {
        return strtolower($request->method()) === "post";
    }

    public static function GetRequest(Request $request) {
        return strtolower($request->method()) === "get";
    }

    public static function WeekDay($date= null){
        if(empty($date)) {
            $date = date('Y-m-d');
        }
        return date('l', strtotime($date));
    }

    public static function Time($format="H:i", $date= null){
        if(empty($date)) {
            $date = date('Y-m-d H:i:s');
        }
        return date("$format", strtotime($date));
    }

    public static function mergeArrayKeyValuesToArray(array $items, $key, array $hide=['keys'=>[], 'end_with'=>'', 'start_with'=>''], $removeKey=true) {
        $array = [];
        foreach ($items as $item) {
            $element = $item;
            if(array_key_exists($key, $item))
            {
                foreach ($element[$key] as $column => $value){
                    if(!empty($hide)) {
                        if(!empty($hide['keys'])) {
                            if(in_array($column, $hide['keys'])) {
                                continue;
                            }
                        }

                        if(!empty($hide['end_with'])) {
                            if(str_ends_with($column, $hide['end_with'])) {
                                continue;
                            }
                        }
                    }
                    $element[$column] = $value;
                }

                if($removeKey) {
                    unset($element[$key]);
                }
            }
            $array[] = $element;
        }

        return $array;
    }

    public static function localhost() {
        return \request()->is("http://127.0.0.1*") || \request()->is("https://127.0.0.1*");
    }

    public static function BrowserRequest()
    {
        $browsers = ['Opera', 'Mozilla', 'Firefox', 'Chrome', 'Edge'];

        $userAgent = request()->header('User-Agent');

        $isBrowser = false;

        foreach($browsers as $browser){
            if(strpos($userAgent, $browser) !==  false){
                $isBrowser = true;
                break;
            }
        }

        return $isBrowser;
    }

    public static function RemovePhoneIntlCode($phone, $force=true) {
        if($force) {
            if(ltrim($phone)[0] !== "+") {
                $phone = "+{$phone}";
            }
        }
        return preg_replace(
            '/\+(?:998|996|995|994|993|992|977|976|975|974|973|972|971|970|968|967|966|965|964|963|962|961|960|886|880|856|855|853|852|850|692|691|690|689|688|687|686|685|683|682|681|680|679|678|677|676|675|674|673|672|670|599|598|597|595|593|592|591|590|509|508|507|506|505|504|503|502|501|500|423|421|420|389|387|386|385|383|382|381|380|379|378|377|376|375|374|373|372|371|370|359|358|357|356|355|354|353|352|351|350|299|298|297|291|290|269|268|267|266|265|264|263|262|261|260|258|257|256|255|254|253|252|251|250|249|248|246|245|244|243|242|241|240|239|238|237|236|235|234|233|232|231|230|229|228|227|226|225|224|223|222|221|220|218|216|213|212|211|98|95|94|93|92|91|90|86|84|82|81|66|65|64|63|62|61|60|58|57|56|55|54|53|52|51|49|48|47|46|45|44\D?1624|44\D?1534|44\D?1481|44|43|41|40|39|36|34|33|32|31|30|27|20|7|1\D?939|1\D?876|1\D?869|1\D?868|1\D?849|1\D?829|1\D?809|1\D?787|1\D?784|1\D?767|1\D?758|1\D?721|1\D?684|1\D?671|1\D?670|1\D?664|1\D?649|1\D?473|1\D?441|1\D?345|1\D?340|1\D?284|1\D?268|1\D?264|1\D?246|1\D?242|1)\D?/'
            , ''
            , "{$phone}"
        );
    }

    public static function GuzzleException(Exception $exception, $show_exception=false) {
        $error = "Oops something bad happened.";
        switch ((int) $exception->getCode()) {
            case 400:
            default:
                $error = "Internal server error";
                break;
        }

        if(env("APP_DEBUG") || $show_exception) {
            $error .= " {$exception->getMessage()}";
        }

        return $error;
    }


    public static function DBUniqueValue($table, $column, $prefix="", $postfix="")
    {
        $uniqueString = self::UID();
        $uniqueString = "{$prefix}{$uniqueString}{$postfix}";

        // call the same function if the unique string exists already
        if(!empty($table) && !empty($column)){

            if (!self::IsUnique($uniqueString, $table, $column)) {
                return self::UID();
            }
        }

        return $uniqueString;
    }

    // NEW METHODS
    public static function XMLFromArray(array $list, $root_tag, $set_headers=false) {
        $xmlObject = new SimpleXMLElement("<{$root_tag}/>");
        self::Array2XML($xmlObject, $list);
        if($set_headers) {
            header('Content-type: text/xml');
        }
        return $xmlObject->asXML();
    }

    public static function Array2XML(SimpleXMLElement $object, array $data, $level = 0)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                #$new_object = $object->addChild(($level == 0) ? 'marker' : $key);
                $new_object = $object->addChild($key);
                self::Array2XML($new_object, $value, $level + 1);
            } else {
                $object->addChild($key, $value);
            }
        }
    }

    public static function HasPrefix($string, $prefix): bool
    {
        return substr($string, 0, strlen($prefix)) === $prefix;
    }

    public static function StartsWith($haystack, $needle): bool
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    public static function EndsWith($haystack, $needle): bool
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /*PROJECT SPECIFIC*/
    public static function SubscriptionDaysRemaining($user) {
        $status = 'paid';

        return $user->subscriptions()->whereRaw("
            TIME_TO_SEC(TIMEDIFF(aa_user_subscriptions.end_date, NOW())) > 0
            AND aa_user_subscriptions.status = '$status'
            ")->selectRaw("
            aa_user_subscriptions.is_trial,
            TIME_TO_SEC(TIMEDIFF(aa_user_subscriptions.end_date, NOW())) seconds,
            DATEDIFF(aa_user_subscriptions.end_date, NOW()) days,
            TIMEDIFF(aa_user_subscriptions.end_date, NOW()) time
            ")->orderBy('id', 'DESC')->first();
    }

    public static function UserHasActiveSubscription($user): bool {
        $remainingDays = self::SubscriptionDaysRemaining($user);
        return  (!empty($remainingDays) && (int) $remainingDays->seconds > 0);
    }

    public static function numberToWords($number)
    {
        $words = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen', 'Twenty'
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $millions = ['', 'Thousand', 'Million', 'Billion'];

        if ($number == 0) return 'Zero';

        $num = (int)$number;
        $result = '';
        $i = 0;

        while ($num > 0) {
            $chunk = $num % 1000;
            if ($chunk > 0) {
                $chunkWords = '';
                $h = (int)($chunk / 100);
                $r = $chunk % 100;
                if ($h > 0) $chunkWords .= $words[$h] . ' Hundred ';
                if ($r > 0) {
                    if ($r <= 20) $chunkWords .= $words[$r] . ' ';
                    else {
                        $chunkWords .= $tens[(int)($r / 10)] . ' ';
                        if ($r % 10 > 0) $chunkWords .= $words[$r % 10] . ' ';
                    }
                }
                $result = $chunkWords . $millions[$i] . ' ' . $result;
            }
            $num = (int)($num / 1000);
            $i++;
        }

        return trim($result);
    }

}

<?php
// exec('python3 duplicate.py');
// exit();
    defined("DEV") || define("DEV", "JCB19960418VYEPEWA4");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: access");
    header("Access-Control-Allow-Methods: GET");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Max-Age: 86400");
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
        header("HTTP/1.1 200 OK");
        # return DEV
        die();
    }

    function response($status, $message="Operation Success", $data=[], $type="json") {
        switch($type){
            case "json": default: echo json_encode(["STATUS"=>$status, "MSG"=>$message, "DATA"=>$data]); break;
        }
        return;
    }

    function request() {
        return [
            "mode"=> $_REQUEST["mode"], 
            "barcode"=> $_REQUEST["barcode"],
            "facility"=> $_REQUEST["facility"],
            "identifier" => $_REQUEST["identifier"],
            "quantity"=> $_REQUEST["quantity"],
            "copies"=> $_REQUEST["copies"],
            "date"=> $_REQUEST["date"],
            "time"=> $_REQUEST["time"],
            "folder"=> $_REQUEST["folder"], 
            "close_tag"=> $_REQUEST["close_tag"],
        ];
    }

    function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }


    function printedBarcodes($filename) {
        chmod($filename, 0777);
        $handle = fopen($filename, "r");
        $barcodes = [];
        if ($handle) {
            $set = [];
            while (($line = fgets($handle)) !== false) {
                // process the line read.
                if(contains("::", $line )) {
                    $barcodes[] = $set;
                    $set = [];
                } else {
                    if(!empty($line)) {
                        $set[] = trim(preg_replace('/\s\s+/', '', $line));
                    }
                }
            }

            fclose($handle);
        }

        return $barcodes;
    }

    extract(request());

    if($mode) {

        switch($mode) {
            case "log":
                $date_barcodes = null;
                try{
                    $date_barcodes = printedBarcodes("./data/production/{$folder}/{$date}.csv");
                }
                catch(Exception $e) {
                    return response(0, "Failed to get Barcodes: {$e->getMessage()}", request()); 
                }
                return response(1, "Barcodes Printed on {$date} Retrived", ["barcodes"=>$date_barcodes]);
            case "duplicate":
                try {
                    $command = "python3 ./duplicate.py '{$barcode}' '{$copies}' '{$date}' '$time' '{$close_tag}'";
                    exec($command, $output, $return_var);
                }
                catch(Exception $e) {
                    return response(0, "Failed to Print: {$e->getMessage()}", request()); 
                }

                return response(1, "Duplicate Barcode Printed", ["printed_barcodes"=>printedBarcodes("./data/production/duplicate/{$date}.csv")]);
            case "new": default;
                try {
                    # FACILITY BARCODE_IDENTIFIER NUMBER_OF_COPIES SERVER_DATE SERVER_TIME CLOSE_TAG<1,0>
                    $command = "python3 ./new.py '{$facility}' '{$identifier}' '{$copies}' '{$date}' '$time' '{$close_tag}'";
                    exec($command, $output, $return_var);
                }
                catch(Exception $e) {
                    return response(0, "Failed to Print: {$e->getMessage()}", request()); 
                }

               return response(1, "Operation Completed Successfully", ["printed_barcodes"=>printedBarcodes("./data/production/new/{$date}.csv")]);
        }
    } 
    else {
        return response(0, "Sorry, Operation cannot be complited mode is not specified!!", ["mode"=>$mode]);
    }
    

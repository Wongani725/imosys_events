<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    public function index(Request $request)
    {
        $event_id = $request->id;
        $event_name = DB::table('events')->where('event_id', $request->id)->value('event_name');

        $data = DB::table('event_participants')->orderBy('reference_code', 'DESC')->paginate(5);
        return view('fileupload.index', ['event_name' => $event_name, 'event_id' => $event_id, 'data' => $data]);
    }

    public function importData(Request $request)
    {
        $this->validate($request, [
            'uploaded_file' => 'required|file|mimes:xls,xlsx'
        ]);

        $the_file = $request->file('uploaded_file');

        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $row_limit = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();
            $row_range = range(3, $row_limit);
            $column_range = range('F', $column_limit);

            $data = array();
            $uniqueCodes = array(); // Store unique codes temporarily

            $event_id = $request->input('event_id');

            foreach ($row_range as $row) {
                $reference = $sheet->getCell('B' . $row)->getValue();

                $balance = $sheet->getCell('AB' . $row)->getValue();

                $data[] = [
                    'event_id' => $event_id,
                    'participant' => $sheet->getCell('C' . $row)->getValue(),
                    'reference_code' => $reference,
                    'phone_number' => $sheet->getCell('E' . $row)->getValue(),
                    'email_address' => $sheet->getCell('D' . $row)->getValue(),
                    'company_name' => $sheet->getCell('G' . $row)->getValue(),
                    'status' => $sheet->getCell('H' . $row)->getValue(),
                    'attire_type' => $sheet->getCell('I' . $row)->getValue(),
                    'attire_size' => $sheet->getCell('J' . $row)->getValue(),
                    'room_type' => $sheet->getCell('L' . $row)->getValue(),
                    'room_number' => $sheet->getCell('M' . $row)->getValue(),
                    'extra_meals' => $sheet->getCell('Q' . $row)->getValue(),
                    'no_of_extra_bed' => $sheet->getCell('W' . $row)->getValue(),
                    'date_paid' => $sheet->getCell('AD' . $row)->getValue(),
                    'hotel' => $sheet->getCell('K' . $row)->getValue(),
                    'invoice_reference' => $sheet->getCell('F' . $row)->getValue(),
                    'balance' => $balance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Generate unique codes based on reference code if balance is 0
                if ($balance == 0 && !isset($uniqueCodes[$reference])) {
                    $uniqueCodes[$reference] = $this->generateUniqueCodes($reference, 5);
                }
            }

            // Insert the participant data into the database
            DB::table('event_participants')->insert($data);

            // Insert the unique codes into the database
            foreach ($uniqueCodes as $reference => $codes) {
                $uniqueCodeArray = json_encode($codes); // Convert the array of codes to JSON format
                DB::table('meal_coupon')->insert([
                    'participant_reference_code' => $reference,
                    'unique_code' => $uniqueCodeArray,
                ]);
            }

            return redirect()->route('fileupload.index', ['id' => $event_id])->with('success', 'Data imported successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while importing the data.');
        }
    }

    public function generateUniqueCodes($referenceCode, $count)
    {
        $uniqueCodes = [];
        for ($i = 1; $i <= $count; $i++) {
            $uniqueCode = $referenceCode . '_' . Str::random(10) . '.jpg';
            $uniqueCodes[] = $uniqueCode;
        }
        return $uniqueCodes;
    }
}

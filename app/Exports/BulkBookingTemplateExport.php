<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;

class BulkBookingTemplateExport implements FromArray, WithHeadings, WithEvents
{
    protected $headings;
    protected $data;
    protected $hotelNames;
    protected $attireSizes;

    public function __construct(array $headings, array $data, array $hotelNames = [], array $attireSizes = [])
    {
        $this->headings = $headings;
        $this->data = $data;
        $this->hotelNames = $hotelNames;
        $this->attireSizes = $attireSizes;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->data;
    }

    protected function addDropdown($sheet, $range, $values, $title, $prompt)
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setFormula1('"' . implode(',', $values) . '"');
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle($title);
        $validation->setPrompt($prompt);
        $validation->setShowErrorMessage(true);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setErrorTitle('Invalid Value');
        $validation->setError('Please select a valid value from the list.');

        $sheet->setDataValidation($range, $validation);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();

                // Highlight example rows in gold
                $sheet->getStyle('A3:K8')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFF0DB');

                // Bold the header
                $sheet->getStyle('A1:K1')->getFont()->setBold(true);

                // --- Data Validation on Sheet 1 ---
                $events = \App\Models\Event::where('event_status', 'active')->orderBy('start_date')->get();
                $eventNames = $events->pluck('event_name')->toArray();
                if (!empty($eventNames)) {
                    $this->addDropdown($sheet, 'A2:A200', $eventNames, 'Select Event', 'Choose the event for this registration.');
                } else {
                    $sheet->setCellValue('A2', 'Governance Forum');
                }

                $this->addDropdown($sheet, 'B2:B200', ['Member', 'Non-Member'], 'Member Status', 'Select Member or Non-Member.');

                $this->addDropdown($sheet, 'G2:G200', ['Yes', 'No'], 'Accommodation', 'Select Yes if accommodation is required.');

                $allHotels = \App\Models\Hotel::orderBy('name')->pluck('name')->unique()->toArray();
                if (!empty($allHotels)) {
                    $this->addDropdown($sheet, 'H2:H200', $allHotels, 'Select Hotel', 'Choose a hotel from the list.');
                }

                $this->addDropdown($sheet, 'I2:I200', ['Yes', 'No'], 'Spouse Included', 'Select Yes if bringing a spouse.');

                $allAttire = \App\Models\AttireSize::orderBy('name')->pluck('name')->unique()->toArray();
                if (!empty($allAttire)) {
                    $this->addDropdown($sheet, 'K2:K200', $allAttire, 'Attire Size', 'Select your attire size.');
                }

                // --- Info Sheet ---
                $infoSheet = new Worksheet($spreadsheet, 'Info');
                $spreadsheet->addSheet($infoSheet);

                $infoSheet->mergeCells('A1:E1');
                $infoSheet->mergeCells('A3:E3');
                $infoSheet->mergeCells('A5:E5');

                $infoSheet->getColumnDimension('A')->setWidth(20);
                $infoSheet->getColumnDimension('B')->setWidth(25);
                $infoSheet->getColumnDimension('C')->setWidth(25);
                $infoSheet->getColumnDimension('D')->setWidth(25);
                $infoSheet->getColumnDimension('E')->setWidth(25);

                $logoPath = public_path('images/alogo2.jpeg');
                if (file_exists($logoPath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(80);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(120);
                    $drawing->setWorksheet($infoSheet);
                }

                $infoSheet->setCellValue('A3', 'Bulk Booking Template');
                $infoSheet->getStyle('A3')->getFont()->setBold(true)->setSize(16);
                $infoSheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $infoSheet->setCellValue('A5', 'Instructions:');
                $infoSheet->getStyle('A5')->getFont()->setBold(true);

                $infoSheet->setCellValue('A6', '1. Rows in gold are example scenarios — replace with your actual data.');
                $infoSheet->setCellValue('A7', '2. Spouse and Extra Guests only apply when Accommodation is Yes.');
                $infoSheet->setCellValue('A8', '3. Member ID is required for Members, leave blank for Non-Members.');
                $infoSheet->setCellValue('A9', '4. Fill in rows starting from row 9. Delete example rows before upload.');

                $infoSheet->getRowDimension('1')->setRowHeight(90);
                $infoSheet->getRowDimension('3')->setRowHeight(30);

                $spreadsheet->setActiveSheetIndex(0);
            },
        ];
    }
}

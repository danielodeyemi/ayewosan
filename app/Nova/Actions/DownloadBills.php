<?php

namespace App\Nova\Actions;

use App\Models\Bills;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Laravel\Nova\Http\Requests\NovaRequest;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class DownloadBills extends DownloadExcel implements FromQuery, ShouldAutoSize, WithEvents
{
    public function name()
    {
        return $this->name ?? __('Export as PDF');
    }

    public function query()
    {
        $selectedResourceIds = [];

        if ($this->request->get('resources') === 'all') {
            // If "All Matching" was selected, get the IDs from the `viaResourceId` parameter
            // $selectedResourceIds = $this->request->viaResourceId;
            // If "All Matching" was selected, return all bills
            return Bills::query();
        } else {
            // Otherwise, get the IDs from the `resources` parameter
            $selectedResourceIds = $this->request->get('resources');

            // If the resources parameter is a string, explode it into an array
            if (is_string($selectedResourceIds)) {
                $selectedResourceIds = explode(',', $selectedResourceIds);
            }
        }

        // If $selectedResourceIds is not an array, return an empty query
        if (!is_array($selectedResourceIds)) {
            return Bills::query()->whereNull('id');
        }

        return Bills::whereIn('id', $selectedResourceIds);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $highestRow = $event->sheet->getHighestRow();

                // Loop through the cells in column C
                for ($row = 2; $row <= $highestRow; $row++) {
                    // Get the cell
                    $cell = $event->sheet->getCell('C' . $row);

                    // Get the value of the cell
                    $value = $cell->getValue();

                    // Try to create a DateTime object from the value
                    try {
                        $dateTime = new \DateTime($value);
                        $formattedDate = $dateTime->format('Y-m-d');
                        $cell->setValue($formattedDate);
                    } catch (\Exception $e) {
                        // If the value is not a valid date, do nothing
                    }
                }

                $highestColumn = $event->sheet->getHighestColumn();

                // Center the entire content horizontally
                $event->sheet->getStyle('A1:' . $highestColumn . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Apply styles to the header row
                $event->sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'bold' => true,
                        'color' => ['rgb' => '000000'],
                        'size' => 10,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Apply styles to the data rows
                $event->sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray([
                    'font' => [
                        'size' => 10, // Reduce the font size
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Center the table on the page
                $event->sheet->getDelegate()->getPageSetup()
                    ->setHorizontalCentered(true)
                    ->setVerticalCentered(true);
            },
        ];
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}

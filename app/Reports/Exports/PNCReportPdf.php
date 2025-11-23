<?php

namespace App\Reports\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class PNCReportPdf
{
    /**
     * Generate and download PNC PDF.
     *
     * @param  \Illuminate\Support\Collection|array  $data
     * @param  string|null  $responsible
     * @return \Illuminate\Http\Response
     */
    public static function generate($data, ?string $responsible = null)
    {
        $viewData = [
            'data' => $data,
            'responsible' => $responsible,
            'generated_at' => now()->toDateTimeString(),
        ];

        // resources/views/reports/pnc.blade.php (you must create this)
        $pdf = Pdf::loadView('reports.pnc', $viewData)
                  ->setPaper('a4', 'portrait');

        return $pdf->download('pnc_report_'.now()->format('Ymd_His').'.pdf');
    }
}

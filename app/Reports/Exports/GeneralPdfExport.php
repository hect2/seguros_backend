<?php

namespace App\Reports\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class GeneralPdfExport
{
    public static function generate($data, $view = 'reports.general', $filename = null)
    {
        $filename = $filename ?? 'report_'.now()->format('Ymd_His').'.pdf';
        $pdf = Pdf::loadView($view, [
            'data' => $data,
            'generated_at' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}

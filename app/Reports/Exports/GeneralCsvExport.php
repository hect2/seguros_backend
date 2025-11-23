<?php

namespace App\Reports\Exports;

use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneralCsvExport
{
    /**
     * Stream a CSV download.
     *
     * @param  array|\Illuminate\Support\Collection  $rows
     * @param  string  $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public static function download($rows, $filename = 'export.csv'): StreamedResponse
    {
        $rows = collect($rows)->map(function ($r) {
            if (is_object($r)) return (array) $r;
            return $r;
        });

        $first = $rows->first();
        $headers = $first ? array_keys((array) $first) : [];

        $callback = function () use ($rows, $headers) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            if ($headers) fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_values((array) $row));
            }

            fclose($handle);
        };

        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream($callback, 200, $responseHeaders);
    }
}

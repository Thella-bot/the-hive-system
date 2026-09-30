<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Response;

/**
 * Streams a CSV download.
 *
 * Every export in the system goes through here so the BOM, headers and content
 * type stay consistent, and so nothing has to buffer a large result set in
 * memory first.
 */
class CsvExporter
{
    /**
     * @param  iterable<int, array<string, scalar|null>>  $rows
     * @param  array<int, string>  $headers
     */
    public function download(iterable $rows, array $headers, string $filename): mixed
    {
        return Response::streamDownload(function () use ($rows, $headers) {
            $file = fopen('php://output', 'w');

            // Byte order mark, so Excel opens UTF-8 names correctly.
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, array_map(
                    fn ($value) => $value === null ? '' : (string) $value,
                    array_values($row)
                ));
            }

            fclose($file);
        }, $this->filename($filename), [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    /**
     * Turn a resource name into a timestamped filename.
     */
    public function filename(string $base): string
    {
        return preg_replace('/[^a-z0-9_\-]/', '_', strtolower($base))
            .'_'.now()->format('Y-m-d_His').'.csv';
    }

    /**
     * Format a date column for a spreadsheet.
     */
    public function date(mixed $value, string $format = 'Y-m-d'): string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->format($format) : '';
    }

    /**
     * Format a money column with two decimal places.
     */
    public function money(mixed $value): string
    {
        return $value === null ? '' : number_format((float) $value, 2, '.', '');
    }
}

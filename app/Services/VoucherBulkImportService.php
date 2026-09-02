<?php

namespace App\Services;

use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class VoucherBulkImportService
{
    /**
     * Import multiple vouchers from a CSV or XLSX file using base configuration settings.
     *
     * @param string $filePath Absolute path or Storage path to the uploaded file
     * @param array $baseConfig Common voucher configuration (discount_type, discount_value, etc.)
     * @return array Result stats ['created' => int, 'skipped' => int, 'errors' => array, 'created_codes' => array]
     */
    public function import(string $filePath, array $baseConfig): array
    {
        $rows = $this->extractRows($filePath);

        if (empty($rows)) {
            return [
                'created' => 0,
                'skipped' => 0,
                'errors' => ['The uploaded file is empty or could not be read.'],
                'created_codes' => [],
            ];
        }

        // Extract header row
        $rawHeaders = array_shift($rows);
        $headers = array_map(fn ($h) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', (string) $h))), $rawHeaders);

        $created = 0;
        $skipped = 0;
        $errors = [];
        $createdCodes = [];

        foreach ($rows as $rowIndex => $row) {
            $lineNum = $rowIndex + 2;

            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue; // Skip blank lines
            }

            // Map row associative array
            $rowAssoc = [];
            foreach ($headers as $colIndex => $headerName) {
                if (isset($row[$colIndex])) {
                    $rowAssoc[$headerName] = trim((string) $row[$colIndex]);
                }
            }

            // Resolve Code and Name
            $code = $this->getValue($rowAssoc, ['code', 'vouchercode', 'voucher_code', 'couponcode', 'coupon', 'id']);
            $name = $this->getValue($rowAssoc, ['name', 'vouchername', 'voucher_name', 'title', 'description', 'label']);

            if (blank($code)) {
                $skipped++;
                $errors[] = "Row {$lineNum}: Missing voucher code.";
                continue;
            }

            $cleanCode = strtoupper(trim($code));

            // Validate code regex: letters, numbers, hyphens, underscores
            if (! preg_match('/^[A-Z0-9_-]+$/i', $cleanCode)) {
                $skipped++;
                $errors[] = "Row {$lineNum} ({$cleanCode}): Code contains invalid characters. Only letters, numbers, hyphens, and underscores are allowed.";
                continue;
            }

            // Check if code already exists in DB or within current batch
            if (Voucher::where('code', $cleanCode)->exists() || in_array($cleanCode, $createdCodes, true)) {
                $skipped++;
                $errors[] = "Row {$lineNum} ({$cleanCode}): Voucher code already exists.";
                continue;
            }

            $cleanName = filled($name) ? trim($name) : $cleanCode;

            try {
                // Prepare attributes from base configuration
                $discountType = $this->getValue($rowAssoc, ['discounttype', 'type']) ?? ($baseConfig['discount_type'] ?? 'percentage');
                $discountValue = $this->getValue($rowAssoc, ['discountvalue', 'discount', 'value']) ?? ($baseConfig['discount_value'] ?? 10.00);
                $maxDiscount = $this->getValue($rowAssoc, ['maxdiscount', 'max_discount', 'cap']) ?? ($baseConfig['max_discount'] ?? null);
                $minBookingAmount = $this->getValue($rowAssoc, ['minbookingamount', 'min_booking_amount', 'min_amount']) ?? ($baseConfig['min_booking_amount'] ?? null);

                $voucher = new Voucher();
                $voucher->code = $cleanCode;
                $voucher->name = $cleanName;
                $voucher->description = $this->getValue($rowAssoc, ['notes', 'description']) ?? ($baseConfig['description'] ?? null);
                $voucher->discount_type = in_array($discountType, ['percentage', 'fixed'], true) ? $discountType : 'percentage';
                $voucher->discount_value = floatval(preg_replace('/[^0-9.]/', '', (string) $discountValue));
                $voucher->max_discount = filled($maxDiscount) ? floatval(preg_replace('/[^0-9.]/', '', (string) $maxDiscount)) : null;
                $voucher->min_booking_amount = filled($minBookingAmount) ? floatval(preg_replace('/[^0-9.]/', '', (string) $minBookingAmount)) : null;
                $voucher->eligible_scope = $baseConfig['eligible_scope'] ?? 'booking_total';

                // Dates
                $voucher->start_at = filled($baseConfig['start_at'] ?? null) ? Carbon::parse($baseConfig['start_at']) : null;
                $voucher->end_at = filled($baseConfig['end_at'] ?? null) ? Carbon::parse($baseConfig['end_at']) : null;

                // Flags & Limits
                $voucher->is_active = $baseConfig['is_active'] ?? true;
                $voucher->is_hidden = $baseConfig['is_hidden'] ?? false;
                $voucher->total_usage_limit = filled($baseConfig['total_usage_limit'] ?? null) ? intval($baseConfig['total_usage_limit']) : null;
                $voucher->one_use_per_customer = $baseConfig['one_use_per_customer'] ?? true;

                // Restrictions
                $voucher->eligible_operator_id = $baseConfig['eligible_operator_id'] ?? null;
                $voucher->eligible_origin = filled($baseConfig['eligible_origin'] ?? null) ? trim($baseConfig['eligible_origin']) : null;
                $voucher->eligible_destination = filled($baseConfig['eligible_destination'] ?? null) ? trim($baseConfig['eligible_destination']) : null;
                $voucher->eligible_schedule_id = $baseConfig['eligible_schedule_id'] ?? null;

                $voucher->save();

                $created++;
                $createdCodes[] = $cleanCode;
            } catch (Throwable $e) {
                $skipped++;
                $errors[] = "Row {$lineNum} ({$cleanCode}): " . $e->getMessage();
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'created_codes' => $createdCodes,
        ];
    }

    /**
     * Extract rows from CSV or XLSX file.
     */
    protected function extractRows(string $filePath): array
    {
        if (! file_exists($filePath)) {
            // Check if relative to storage
            if (Storage::disk('local')->exists($filePath)) {
                $filePath = Storage::disk('local')->path($filePath);
            } else {
                throw new \InvalidArgumentException("File not found at: {$filePath}");
            }
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'xlsx') {
            return $this->parseXlsxRows($filePath);
        }

        return $this->parseCsvRows($filePath);
    }

    /**
     * Parse rows from CSV file.
     */
    protected function parseCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException('Could not open CSV file.');
        }

        // Remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Parse rows from XLSX natively using ZipArchive & SimpleXML.
     */
    protected function parseXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Unable to open XLSX file ZIP archive.');
        }

        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xmlStr = $zip->getFromIndex($index);
            $xml = @simplexml_load_string($xmlStr);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string) $val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $run) {
                            $text .= (string) $run->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
        if ($sheetIndex === false) {
            $zip->close();
            throw new \RuntimeException('No worksheet XML found in XLSX file.');
        }

        $xmlStr = $zip->getFromIndex($sheetIndex);
        $xml = @simplexml_load_string($xmlStr);
        $zip->close();

        if (! $xml || ! isset($xml->sheetData)) {
            throw new \RuntimeException('Invalid worksheet XML structure.');
        }

        $allRows = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $rowCells = [];
            foreach ($rowNode->c as $cellNode) {
                $ref = (string) $cellNode['r'];
                $colLetters = preg_replace('/[0-9]/', '', $ref);
                $colIndex = $this->columnLetterToIndex($colLetters);

                $val = (string) $cellNode->v;
                $type = (string) $cellNode['t'];

                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $cellValue = $sharedStrings[(int) $val];
                } else {
                    $cellValue = $val;
                }

                $rowCells[$colIndex] = $cellValue;
            }

            if (! empty($rowCells)) {
                ksort($rowCells);
                $maxIndex = max(array_keys($rowCells));
                $denseRow = [];
                for ($i = 0; $i <= $maxIndex; $i++) {
                    $denseRow[$i] = $rowCells[$i] ?? '';
                }
                $allRows[] = $denseRow;
            }
        }

        return $allRows;
    }

    protected function columnLetterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $len = strlen($letters);
        $index = 0;

        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    /**
     * Helper to retrieve value by multiple candidate keys.
     */
    protected function getValue(array $row, array $candidateKeys): ?string
    {
        foreach ($candidateKeys as $key) {
            if (isset($row[$key]) && (string) $row[$key] !== '') {
                return trim((string) $row[$key]);
            }
        }

        return null;
    }
}

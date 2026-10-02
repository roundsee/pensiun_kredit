<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Throwable;

/**
 * Normalisasi nilai yang dikirim Google Apps Script.
 *
 * Apps Script tidak selalu mengirim tipe yang rapi: sel bertanggal bisa jadi
 * objek Date (lolos ke payload sebagai string ISO 8601) atau string hasil
 * `getDisplayValues()` ("05/01/2026"), dan angka bisa jadi string berformat
 * ("Rp 1.500.000" / "1,500,000.00"). Semua bentuk itu dinormalkan di sini
 * supaya controller hanya perlu memvalidasi nilai yang sudah benar tipenya.
 */
trait NormalizesSheetValues
{
    /**
     * Format tanggal tanpa jam yang dikenali. `d/m/Y` dicoba sebelum `m/d/Y`
     * karena sheet ini dipakai tim Indonesia.
     *
     * @var list<string>
     */
    private array $sheetDateFormats = [
        'Y-m-d',
        'd/m/Y',
        'd-m-Y',
        'd.m.Y',
        'Y/m/d',
        'd M Y',
        'd/m/Y H:i:s',
    ];

    /** Epoch dasar serial number Google Sheets (30 Desember 1899). */
    private string $sheetsSerialEpoch = '1899-12-30';

    /**
     * Ubah nilai tanggal apa pun menjadi string `Y-m-d`, atau null kalau kosong
     * atau tidak bisa dibaca sebagai tanggal.
     */
    private function normalizeSheetDate(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        // Serial number Google Sheets: 1 = 31 Desember 1899.
        if (is_int($value) || is_float($value)) {
            return Carbon::parse($this->sheetsSerialEpoch)
                ->addDays((int) $value)
                ->format('Y-m-d');
        }

        if (! is_string($value)) {
            return null;
        }

        $text = trim($value);

        if ($text === '') {
            return null;
        }

        // ISO 8601, mis. hasil JSON.stringify(new Date(...)) dari Apps Script.
        if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?(\.\d+)?(Z|[+-]\d{2}:?\d{2})?$/', $text) === 1) {
            return $this->formatParsedDate($text, $this->safeParse($text));
        }

        foreach ($this->sheetDateFormats as $format) {
            $parsed = $this->safeCreateFromFormat($format, $text);

            if ($parsed === null || ! $this->dateMatches($parsed, $format, $text)) {
                continue;
            }

            return $parsed->format('Y-m-d');
        }

        return null;
    }

    /**
     * Ubah nilai angka apa pun menjadi float, atau null kalau kosong/tidak
     * terbaca. Pemisah ribuan dan desimal ditangani terpisah supaya
     * "1,500,000.00" dan "1.500.000,50" sama-sama benar.
     */
    private function normalizeSheetNumber(mixed $value): ?float
    {
        if ($value === null || $value === '' || is_bool($value)) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        // Gaya akuntansi: (1.500.000) berarti negatif.
        $negative = str_starts_with($text = trim($value), '(') && str_ends_with($text, ')');
        $text = trim(str_ireplace(['rp', 'idr'], '', $text));
        $text = str_replace(['(', ')', ' ', "\u{00A0}", '_'], '', $text);

        if ($text === '' || $text === '-') {
            return null;
        }

        if (preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $text) === 1) {
            // Titik = ribuan, koma = desimal (konvensi Indonesia).
            $text = str_replace(['.', ','], ['', '.'], $text);
        } elseif (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $text) === 1) {
            // Koma = ribuan, titik = desimal.
            $text = str_replace(',', '', $text);
        } elseif (substr_count($text, ',') === 1) {
            $text = str_replace(',', '.', $text);
        } elseif (substr_count($text, '.') > 1) {
            $text = str_replace('.', '', $text);
        }

        if (! is_numeric($text)) {
            return null;
        }

        $number = (float) $text;

        return $negative ? -$number : $number;
    }

    /**
     * Rapikan teks: trim, string kosong jadi null, dipotong sesuai panjang kolom.
     */
    private function normalizeSheetText(mixed $value, int $maxLength): ?string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return null;
        }

        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, $maxLength);
    }

    /**
     * `createFromFormat` menerima nilai overflow ("31/02/2026" jadi 2 Maret),
     * jadi hasilnya dicocokkan lagi dengan string aslinya supaya tanggal tidak
     * valid ditolak, bukan diam-diam digeser.
     */
    private function dateMatches(Carbon $parsed, string $format, string $original): bool
    {
        $strip = static fn (string $value): string => preg_replace('/[^0-9a-zA-Z]/', '', $value) ?? '';

        return strcasecmp($strip($original), $strip($parsed->format($format))) === 0;
    }

    private function safeCreateFromFormat(string $format, string $value): ?Carbon
    {
        try {
            $parsed = Carbon::createFromFormat($format, $value);
        } catch (Throwable) {
            return null;
        }

        return $parsed instanceof Carbon ? $parsed : null;
    }

    private function safeParse(string $value): ?Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function formatParsedDate(string $original, ?Carbon $parsed): ?string
    {
        if ($parsed === null) {
            return null;
        }

        // Untuk ISO 8601, bagian tanggal harus persis sama supaya "2026-02-31"
        // tidak diam-diam jadi 2 Maret.
        $originalDate = substr($original, 0, 10);

        return $originalDate === $parsed->format('Y-m-d') ? $parsed->format('Y-m-d') : null;
    }
}
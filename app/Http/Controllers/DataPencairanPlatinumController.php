<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesSheetValues;
use App\Models\DataPencairanPlatinum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * API sinkronisasi data pencairan platinum dari Google Apps Script.
 *
 * Kontrak:
 *  - POST /api/pencairan-platinum/sync dengan body `{"rows": [ ... ]}`.
 *  - Auth: `Authorization: Bearer <PENCAIRAN_PLATINUM_API_TOKEN>`.
 *  - Baris di-upsert berdasarkan `no_pk`. Kirim ulang baris yang sama tidak
 *    menghasilkan duplikat, tapi menimpa data lama dengan nilai terbaru.
 *  - Baris yang gagal divalidasi tidak menggagalkan baris lain; detailnya
 *    dikembalikan di `errors`. Kalau tidak ada satu pun baris yang tersimpan,
 *    response-nya 422 supaya sisi Google bisa berhenti dan memperingatkan
 *    orang, bukan retry tanpa henti.
 */
class DataPencairanPlatinumController extends Controller
{
    use NormalizesSheetValues;

    /**
     * Sumber kebenaran bentuk kolom: dipakai untuk normalisasi, validasi, dan
     * penyusunan pesan error supaya ketiganya tidak bisa berbeda.
     *
     * @return array<string, array{type: string, length?: int, required?: bool}>
     */
    private function columnSchema(): array
    {
        return [
            'no_urut' => ['type' => 'integer'],
            'tgl_akad' => ['type' => 'date'],
            'nama' => ['type' => 'text', 'length' => 150],
            'nopen' => ['type' => 'text', 'length' => 50],
            'norek_kb' => ['type' => 'text', 'length' => 50],
            'no_tlp_hp' => ['type' => 'text', 'length' => 30],
            'no_pk' => ['type' => 'text', 'length' => 100, 'required' => true],
            'plafond' => ['type' => 'decimal'],
            'tenor' => ['type' => 'integer'],
            'angsuran' => ['type' => 'decimal'],
            'pic' => ['type' => 'text', 'length' => 100],
            'korwil' => ['type' => 'text', 'length' => 100],
            'ao_kb' => ['type' => 'text', 'length' => 100],
            'kota' => ['type' => 'text', 'length' => 100],
            'verif' => ['type' => 'text', 'length' => 50],
            'tgl_cair_dp' => ['type' => 'date'],
            'tgl_cair_pelunasan' => ['type' => 'date'],
            'tgl_cair_sisa_bersih' => ['type' => 'date'],
            'nominal_cair_dp' => ['type' => 'decimal'],
            'nominal_cair_pelunasan' => ['type' => 'decimal'],
            'nominal_cair_sisa_bersih' => ['type' => 'decimal'],
            'pendana' => ['type' => 'text', 'length' => 100],
            'biaya_provisi' => ['type' => 'decimal'],
            'biaya_admin' => ['type' => 'decimal'],
            'biaya_asuransi' => ['type' => 'decimal'],
            'materai_tl' => ['type' => 'decimal'],
            'biaya_flagging' => ['type' => 'decimal'],
            'total_potongan' => ['type' => 'decimal'],
            'angsuran_dimuka' => ['type' => 'decimal'],
            'angsuran_per_bulan_bank' => ['type' => 'decimal'],
            'biaya_adm_angsuran' => ['type' => 'decimal'],
            'total_angsuran' => ['type' => 'decimal'],
            'fee_insentif_persen' => ['type' => 'decimal'],
            'fee_insentif_nominal' => ['type' => 'decimal'],
            'tat_flagging' => ['type' => 'text', 'length' => 50],
            'status_flagging' => ['type' => 'text', 'length' => 100],
            'tgl_kirim_email_flagging' => ['type' => 'text', 'length' => 50],
            'kendala_flagging' => ['type' => 'text'],
            'keterangan_tambahan' => ['type' => 'text'],
        ];
    }

    public function sync(Request $request): JsonResponse
    {
        $maxRows = (int) config('pencairan_platinum.max_rows_per_request', 200);

        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1', 'max:'.$maxRows],
            'rows.*' => ['required', 'array'],
            'full_replace' => ['sometimes', 'boolean'],
        ]);

        $fullReplace = (bool) ($validated['full_replace'] ?? false);

        $inserted = 0;
        $updated = 0;
        $errors = [];
        $ignored = [];

        foreach ($validated['rows'] as $index => $row) {
            $position = $index + 1;

            try {
                $attributes = $this->normalizeRow($row, $errors, $ignored, $position);
            } catch (Throwable $exception) {
                $errors[] = [
                    'row' => $position,
                    'no_pk' => $this->peekNoPk($row),
                    'messages' => ['_row' => 'Baris gagal diproses: '.$exception->getMessage()],
                ];

                continue;
            }

            if ($attributes === null) {
                continue;
            }

            $noPk = $attributes['no_pk'];

            try {
                $existing = DataPencairanPlatinum::where('no_pk', $noPk)->first();

                $payload = $fullReplace
                    ? $this->blankOutMissingColumns($attributes, array_keys($attributes))
                    : $attributes;

                if ($existing !== null) {
                    $existing->fill($payload)->save();
                    $updated++;
                } else {
                    DataPencairanPlatinum::create($payload);
                    $inserted++;
                }
            } catch (Throwable $exception) {
                $errors[] = [
                    'row' => $position,
                    'no_pk' => $noPk,
                    'messages' => ['_db' => 'Gagal menyimpan: '.$exception->getMessage()],
                ];
            }
        }

        $failed = count($errors);
        $persisted = $inserted + $updated;

        return response()->json([
            'success' => $persisted > 0,
            'received' => count($validated['rows']),
            'inserted' => $inserted,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
            'ignored_fields' => array_values(array_unique($ignored)),
        ], $persisted > 0 ? 200 : 422);
    }

    /**
     * Endpoint kecil buat cek token & kapasitas tanpa perlu kirim data.
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Token valid.',
            'max_rows_per_request' => (int) config('pencairan_platinum.max_rows_per_request', 200),
            'total_rows' => DataPencairanPlatinum::count(),
        ]);
    }

    /**
     * Normalisasi + validasi satu baris. Return null kalau baris ditolak.
     *
     * @param  array<int, array<string, string>>  $errors
     * @param  list<string>  $ignored
     * @return array<string, mixed>|null
     */
    private function normalizeRow(array $row, array &$errors, array &$ignored, int $position): ?array
    {
        $schema = $this->columnSchema();
        $attributes = [];
        $messages = [];

        foreach ($row as $key => $value) {
            if (! array_key_exists($key, $schema)) {
                $ignored[] = (string) $key;

                continue;
            }

            $rule = $schema[$key];

            switch ($rule['type']) {
                case 'date':
                    $normalised = $this->normalizeSheetDate($value);

                    if ($normalised === null && $this->hasContent($value)) {
                        $messages[$key] = 'Tanggal tidak dikenali: '.$this->describe($value);

                        continue 2;
                    }

                    $attributes[$key] = $normalised;

                    break;

                case 'decimal':
                    $normalised = $this->normalizeSheetNumber($value);

                    if ($normalised === null && $this->hasContent($value)) {
                        $messages[$key] = 'Angka tidak dikenali: '.$this->describe($value);

                        continue 2;
                    }

                    $attributes[$key] = $normalised;

                    break;

                case 'integer':
                    $normalised = $this->normalizeSheetNumber($value);

                    if ($normalised === null && $this->hasContent($value)) {
                        $messages[$key] = 'Angka tidak dikenali: '.$this->describe($value);

                        continue 2;
                    }

                    if ($normalised !== null && $normalised !== floor($normalised)) {
                        $messages[$key] = 'Harus bilangan bulat, diterima: '.$normalised;

                        continue 2;
                    }

                    $attributes[$key] = $normalised === null ? null : (int) $normalised;

                    break;

                default:
                    $normalised = $this->normalizeSheetText($value, $rule['length'] ?? 65535);
                    $attributes[$key] = $normalised;

                    if (($rule['required'] ?? false) && $normalised === null) {
                        $messages[$key] = 'Wajib diisi.';
                    }

                    break;
            }
        }

        if ($messages !== []) {
            $errors[] = [
                'row' => $position,
                'no_pk' => $this->peekNoPk($row),
                'messages' => $messages,
            ];

            return null;
        }

        if (! isset($attributes['no_pk'])) {
            $errors[] = [
                'row' => $position,
                'no_pk' => $this->peekNoPk($row),
                'messages' => ['no_pk' => 'Wajib diisi.'],
            ];

            return null;
        }

        return $attributes;
    }

    /**
     * Untuk mode `full_replace`: kolom yang tidak dikirim baris ini dikosongkan,
     * karena Google Sheet dianggap sumber kebenaran.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $sentColumns
     * @return array<string, mixed>
     */
    private function blankOutMissingColumns(array $attributes, array $sentColumns): array
    {
        $payload = [];

        foreach (array_keys($this->columnSchema()) as $column) {
            $payload[$column] = in_array($column, $sentColumns, true) ? ($attributes[$column] ?? null) : null;
        }

        return $payload;
    }

    /**
     * Ambil `no_pk` tanpa lewat validasi, cuma untuk pesan error.
     */
    private function peekNoPk(array $row): ?string
    {
        $value = $row['no_pk'] ?? null;

        if (is_scalar($value)) {
            $value = trim((string) $value);

            return $value === '' ? null : $value;
        }

        return null;
    }

    private function hasContent(mixed $value): bool
    {
        if ($value === null || is_bool($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '' && trim($value) !== '-';
        }

        return is_scalar($value);
    }

    private function describe(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : gettype($value);
    }
}
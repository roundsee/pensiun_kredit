<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel wilayah (provinsi/kabupaten/kecamatan/kelurahan).
 *
 * Keempat tabel berbentuk sama — `code`, `parent_code`, `name` — dengan
 * `parent_code` menunjuk ke `code` tingkat di atasnya, jadi satu model cukup
 * untuk semuanya dan cukup dibedakan lewat nama tabelnya.
 *
 * Kolom wilayah di tabel `debitur` (prop_rumah, kel_domisili, dst) disimpan
 * sebagai KODE, bukan nama, supaya cascade dropdown cukup mencocokkan kode dan
 * nama selalu bisa diturunkan dari tabel ini.
 */
class Wilayah extends Model
{
    public const PROVINSI = 'provinsi';
    public const KABUPATEN = 'kabupaten';
    public const KECAMATAN = 'kecamatan';
    public const KELURAHAN = 'kelurahan';

    public const TINGKAT = [
        self::PROVINSI,
        self::KABUPATEN,
        self::KECAMATAN,
        self::KELURAHAN,
    ];

    public $timestamps = false;

    protected $fillable = [
        'code',
        'parent_code',
        'name',
    ];

    public static function tingkatValid(string $tingkat): bool
    {
        return in_array($tingkat, self::TINGKAT, true);
    }

    /**
     * Tingkat tepat satu di bawah `$tingkat`, atau null untuk yang paling bawah.
     */
    public static function tingkatAnak(string $tingkat): ?string
    {
        return match ($tingkat) {
            self::PROVINSI => self::KABUPATEN,
            self::KABUPATEN => self::KECAMATAN,
            self::KECAMATAN => self::KELURAHAN,
            default => null,
        };
    }

    /**
     * Opsi dropdown untuk sebuah tingkat wilayah.
     *
     * @$parentCode null dipakai untuk dropdown provinsi (seluruhnya) dan untuk
     * mengosongkan dropdown anak.
     *
     * @return array<int, array{code: string, name: string}>
     */
    public static function opsi(string $tingkat, ?string $parentCode = null, ?string $search = null): array
    {
        $query = (new static())->setTable($tingkat)->newQuery()->orderBy('name');

        $parentCode = $parentCode === null ? '' : trim($parentCode);

        if ($parentCode !== '') {
            $query->where('parent_code', $parentCode);
        }

        $search = $search === null ? '' : trim($search);

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->get(['code', 'name'])
            ->map(fn (self $row) => [
                'code' => (string) $row->getAttribute('code'),
                'name' => (string) $row->getAttribute('name'),
            ])
            ->all();
    }

    /**
     * Satu tingkat wilayah sebagai pasangan kode => nama, untuk mengisi nilai
     * yang sedang terpilih di form.
     */
    public static function peta(string $tingkat, ?string $parentCode = null): array
    {
        $opsi = static::opsi($tingkat, $parentCode);
        $peta = [];

        foreach ($opsi as $item) {
            $peta[$item['code']] = $item['name'];
        }

        return $peta;
    }
}

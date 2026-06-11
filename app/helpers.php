<?php

if (!function_exists('terbilang_id')) {
    function terbilang_id(mixed $value): string
    {
        $digits = preg_replace('/[^\d]/', '', trim((string) $value));
        $angka = $digits === '' ? 0 : (int) $digits;

        if ($angka === 0) {
            return 'Nol';
        }

        return terbilang_id_rec($angka);
    }
}

if (!function_exists('terbilang_id_rec')) {
    function terbilang_id_rec(int $angka): string
    {
        $dasar = [
            0 => '',
            1 => 'Satu',
            2 => 'Dua',
            3 => 'Tiga',
            4 => 'Empat',
            5 => 'Lima',
            6 => 'Enam',
            7 => 'Tujuh',
            8 => 'Delapan',
            9 => 'Sembilan',
            10 => 'Sepuluh',
            11 => 'Sebelas',
        ];

        if ($angka < 12) {
            return $dasar[$angka];
        }

        if ($angka < 20) {
            return terbilang_id_rec($angka - 10) . ' Belas';
        }

        if ($angka < 100) {
            $puluh = intdiv($angka, 10);
            $sisa = $angka % 10;
            $hasil = terbilang_id_rec($puluh) . ' Puluh';

            if ($sisa > 0) {
                $hasil .= ' ' . terbilang_id_rec($sisa);
            }

            return $hasil;
        }

        if ($angka < 200) {
            $sisa = $angka - 100;
            return $sisa > 0 ? 'Seratus ' . terbilang_id_rec($sisa) : 'Seratus';
        }

        if ($angka < 1000) {
            $ratus = intdiv($angka, 100);
            $sisa = $angka % 100;
            $hasil = terbilang_id_rec($ratus) . ' Ratus';

            if ($sisa > 0) {
                $hasil .= ' ' . terbilang_id_rec($sisa);
            }

            return $hasil;
        }

        if ($angka < 2000) {
            $sisa = $angka - 1000;
            return $sisa > 0 ? 'Seribu ' . terbilang_id_rec($sisa) : 'Seribu';
        }

        if ($angka < 1000000) {
            $ribu = intdiv($angka, 1000);
            $sisa = $angka % 1000;
            $hasil = terbilang_id_rec($ribu) . ' Ribu';

            if ($sisa > 0) {
                $hasil .= ' ' . terbilang_id_rec($sisa);
            }

            return $hasil;
        }

        if ($angka < 1000000000) {
            $juta = intdiv($angka, 1000000);
            $sisa = $angka % 1000000;
            $hasil = terbilang_id_rec($juta) . ' Juta';

            if ($sisa > 0) {
                $hasil .= ' ' . terbilang_id_rec($sisa);
            }

            return $hasil;
        }

        if ($angka < 1000000000000) {
            $miliar = intdiv($angka, 1000000000);
            $sisa = $angka % 1000000000;
            $hasil = terbilang_id_rec($miliar) . ' Miliar';

            if ($sisa > 0) {
                $hasil .= ' ' . terbilang_id_rec($sisa);
            }

            return $hasil;
        }

        $triliun = intdiv($angka, 1000000000000);
        $sisa = $angka % 1000000000000;
        $hasil = terbilang_id_rec($triliun) . ' Triliun';

        if ($sisa > 0) {
            $hasil .= ' ' . terbilang_id_rec($sisa);
        }

        return $hasil;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KbReferenceOptionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['category' => 'area', 'value' => '101 - BANDUNG', 'sort_order' => 1],
            ['category' => 'area', 'value' => '102 - SUMEDANG', 'sort_order' => 2],
            ['category' => 'area', 'value' => '103 - SUBANG', 'sort_order' => 3],
            ['category' => 'area', 'value' => '104 - TASIKMALAYA', 'sort_order' => 4],
            ['category' => 'area', 'value' => '105 - CIANJUR', 'sort_order' => 5],
            ['category' => 'area', 'value' => '106 - CIREBON', 'sort_order' => 6],
            ['category' => 'area', 'value' => '107 - MAJALENGKA', 'sort_order' => 7],
            ['category' => 'area', 'value' => '108 - INDRAMAYU', 'sort_order' => 8],
            ['category' => 'area', 'value' => '109 - SUKABUMI', 'sort_order' => 9],
            ['category' => 'area', 'value' => '110 - KARAWANG', 'sort_order' => 10],
            ['category' => 'area', 'value' => '111 - BOGOR', 'sort_order' => 11],
            ['category' => 'area', 'value' => '112 - GARUT', 'sort_order' => 12],
            ['category' => 'area', 'value' => '113 - PURWAKARTA', 'sort_order' => 13],
            ['category' => 'area', 'value' => '201 - SEMARANG', 'sort_order' => 14],
            ['category' => 'area', 'value' => '202 - TEGAL', 'sort_order' => 15],
            ['category' => 'area', 'value' => '203 - SOLO / SURAKARTA', 'sort_order' => 16],
            ['category' => 'area', 'value' => '204 - SALATIGA', 'sort_order' => 17],
            ['category' => 'area', 'value' => '205 - KENDAL', 'sort_order' => 18],
            ['category' => 'area', 'value' => '206 - SRAGEN', 'sort_order' => 19],
            ['category' => 'area', 'value' => '207 - WONOGIRI', 'sort_order' => 20],
            ['category' => 'area', 'value' => '301 - SURABAYA', 'sort_order' => 21],
            ['category' => 'area', 'value' => '302 - KEDIRI', 'sort_order' => 22],
            ['category' => 'area', 'value' => '303 - MADIUN', 'sort_order' => 23],
            ['category' => 'area', 'value' => '304 - JEMBER', 'sort_order' => 24],
            ['category' => 'area', 'value' => '305 - MALANG', 'sort_order' => 25],
            ['category' => 'area', 'value' => '306 - MOJOKERTO', 'sort_order' => 26],
            ['category' => 'area', 'value' => '307 - SIDOARJO', 'sort_order' => 27],
            ['category' => 'area', 'value' => '401 - SERANG', 'sort_order' => 28],
            ['category' => 'area', 'value' => '402 - TANGERANG', 'sort_order' => 29],
            ['category' => 'area', 'value' => '403 - CILEGON', 'sort_order' => 30],
            ['category' => 'area', 'value' => '501 - JAMBI', 'sort_order' => 31],
            ['category' => 'area', 'value' => '502 - PALEMBANG', 'sort_order' => 32],
            ['category' => 'area', 'value' => '503 - METRO', 'sort_order' => 33],
            ['category' => 'area', 'value' => '504 - BANDAR LAMPUNG', 'sort_order' => 34],
            ['category' => 'area', 'value' => '505 - KOTA BUMI', 'sort_order' => 35],
            ['category' => 'area', 'value' => '506 - PRINGSEWU', 'sort_order' => 36],
            ['category' => 'area', 'value' => '601 - PONTIANAK', 'sort_order' => 37],
            ['category' => 'area', 'value' => '602 - BANJARMASIN', 'sort_order' => 38],
            ['category' => 'area', 'value' => '603 - MARTAPURA', 'sort_order' => 39],
            ['category' => 'area', 'value' => '604 - HULU SUNGAI SELATAN', 'sort_order' => 40],
            ['category' => 'area', 'value' => '605 - KALIMANTAN TENGAH', 'sort_order' => 41],
            ['category' => 'area', 'value' => '701 - KUPANG', 'sort_order' => 42],
            ['category' => 'area', 'value' => '702 - SOE', 'sort_order' => 43],
            ['category' => 'area', 'value' => '703 - ATAMBUA', 'sort_order' => 44],
            ['category' => 'area', 'value' => '704 - SBW DOMPU', 'sort_order' => 45],
            ['category' => 'area', 'value' => '705 - BIMA', 'sort_order' => 46],
            ['category' => 'area', 'value' => '706 - FLORES', 'sort_order' => 47],
            ['category' => 'area', 'value' => '707 - SELONG', 'sort_order' => 48],
            ['category' => 'area', 'value' => '708 - TABANAN', 'sort_order' => 49],
            ['category' => 'area', 'value' => '709 - MATARAM', 'sort_order' => 50],
            ['category' => 'area', 'value' => '710 - SUMBA', 'sort_order' => 51],
            ['category' => 'area', 'value' => '711 - ALOR', 'sort_order' => 52],
            ['category' => 'area', 'value' => '712 - ENDE', 'sort_order' => 53],
            ['category' => 'area', 'value' => '713 - KOMODO', 'sort_order' => 54],
            ['category' => 'area', 'value' => '714 - BALI', 'sort_order' => 55],
            ['category' => 'area', 'value' => '801 - MAKASSAR', 'sort_order' => 56],
            ['category' => 'area', 'value' => '802 - PALU', 'sort_order' => 57],
            ['category' => 'area', 'value' => '803 - MANADO', 'sort_order' => 58],
            ['category' => 'area', 'value' => '804 - MAKASAR', 'sort_order' => 59],
            ['category' => 'area', 'value' => '805 - PAREPARE', 'sort_order' => 60],
            ['category' => 'area', 'value' => '806 - KENDARI', 'sort_order' => 61],
            ['category' => 'area', 'value' => '901 - MERAUKE', 'sort_order' => 62],
            ['category' => 'area', 'value' => '902 - JAYAPURA', 'sort_order' => 63],
            ['category' => 'area', 'value' => '903 - SORONG', 'sort_order' => 64],
            ['category' => 'area', 'value' => '1001 - YOGYAKARTA', 'sort_order' => 65],
            ['category' => 'area', 'value' => '1101 - JAKARTA', 'sort_order' => 66],
            ['category' => 'bank_tujuan', 'value' => 'BANK BUKOPIN', 'sort_order' => 1],
            ['category' => 'bank_tujuan', 'value' => 'BANK MNC', 'sort_order' => 2],
            ['category' => 'bank_tujuan', 'value' => 'BANK MANTAP', 'sort_order' => 3],
            ['category' => 'bank_tujuan', 'value' => 'PT POS INDONESIA', 'sort_order' => 4],
        ];

        foreach ($rows as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        unset($row);

        DB::table('kb_reference_options')->upsert(
            $rows,
            ['category', 'value'],
            ['sort_order', 'updated_at']
        );
    }
}

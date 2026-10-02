<?php

namespace Tests\Feature;

use App\Models\DataPencairanPlatinum;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test endpoint sinkronisasi pencairan platinum dari Google Apps Script.
 *
 * Sengaja memakai transaksi manual (bukan `RefreshDatabase`) supaya jalan di
 * database dev yang sudah termigrasi penuh, mengikuti test debitur yang sudah
 * ada. Tabel `data_pencairan_platinum` sengaja dibiarkan kosong di dev supaya
 * isi test ini tidak tercampur dengan data asli.
 */
class PencairanPlatinumSyncTest extends TestCase
{
    private string $token = 'test-token-pencairan';

    protected function setUp(): void
    {
        parent::setUp();

        // phpunit.xml default-nya mengarah ke database test lama; pakai database dev.
        config(['database.connections.mysql.database' => 'new_pensiun_kredit']);
        config(['database.default' => 'mysql']);
        config(['pencairan_platinum.api_token' => $this->token]);
        DB::purge('mysql');

        DB::connection('mysql')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql')->rollBack();

        parent::tearDown();
    }

    private function payload(array $rows, array $extra = []): array
    {
        return array_merge(['rows' => $rows], $extra);
    }

    public function test_token_tidak_diterima_tanpa_bearer(): void
    {
        $response = $this->postJson('/api/pencairan-platinum/sync', $this->payload([
            ['no_pk' => 'PK-001'],
        ]));

        $response->assertStatus(401);
        $this->assertSame(0, DataPencairanPlatinum::count());
    }

    public function test_token_salah_ditolak(): void
    {
        $this->withHeader('Authorization', 'Bearer token-ngawur')
            ->postJson('/api/pencairan-platinum/sync', $this->payload([['no_pk' => 'PK-001']]))
            ->assertStatus(401);
    }

    public function test_ping_validasi_token(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/pencairan-platinum/ping')
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_insert_baris_satu(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/pencairan-platinum/sync', $this->payload([
                [
                    'no_urut' => 1,
                    'tgl_akad' => '05/01/2026',
                    'nama' => 'Budi Santoso',
                    'nopen' => '0012345678',
                    'no_pk' => 'PK-2026-0001',
                    'plafond' => 'Rp 150.000.000',
                    'tenor' => '36',
                    'angsuran' => 5500000,
                    'tgl_cair_dp' => '2026-01-10',
                    'biaya_provisi' => '1.500.000,50',
                ],
            ]));

        $response->assertOk()->assertJson([
            'success' => true,
            'received' => 1,
            'inserted' => 1,
            'updated' => 0,
            'failed' => 0,
        ]);

        $row = DataPencairanPlatinum::where('no_pk', 'PK-2026-0001')->firstOrFail();

        $this->assertSame('Budi Santoso', $row->nama);
        $this->assertSame('2026-01-05', $row->tgl_akad->format('Y-m-d'));
        $this->assertSame('2026-01-10', $row->tgl_cair_dp->format('Y-m-d'));
        $this->assertSame(36, $row->tenor);
        $this->assertSame('150000000.00', $row->plafond);
        $this->assertSame('5500000.00', $row->angsuran);
        $this->assertSame('1500000.50', $row->biaya_provisi);
    }

    public function test_kirim_ulang_no_pk_yang_sama_menimpa_bukan_menduplikat(): void
    {
        $token = ['Authorization' => 'Bearer '.$this->token];

        $this->withHeaders($token)->postJson('/api/pencairan-platinum/sync', $this->payload([
            ['no_pk' => 'PK-2026-0002', 'nama' => 'Lama', 'plafond' => 1000000],
        ]))->assertOk()->assertJson(['inserted' => 1]);

        $this->withHeaders($token)->postJson('/api/pencairan-platinum/sync', $this->payload([
            ['no_pk' => 'PK-2026-0002', 'nama' => 'Baru', 'plafond' => 2000000],
        ]))->assertOk()->assertJson(['inserted' => 0, 'updated' => 1]);

        $this->assertSame(1, DataPencairanPlatinum::count());

        $row = DataPencairanPlatinum::where('no_pk', 'PK-2026-0002')->firstOrFail();
        $this->assertSame('Baru', $row->nama);
        $this->assertSame('2000000.00', $row->plafond);
    }

    public function test_kolom_yang_tidak_dikirim_tidak_di_overwrite(): void
    {
        $token = ['Authorization' => 'Bearer '.$this->token];

        $this->withHeaders($token)->postJson('/api/pencairan-platinum/sync', $this->payload([
            ['no_pk' => 'PK-2026-0003', 'nama' => 'Lama', 'pic' => 'PIC-A'],
        ]))->assertOk();

        $this->withHeaders($token)->postJson('/api/pencairan-platinum/sync', $this->payload([
            ['no_pk' => 'PK-2026-0003', 'nama' => 'Baru'],
        ]))->assertOk();

        $row = DataPencairanPlatinum::where('no_pk', 'PK-2026-0003')->firstOrFail();
        $this->assertSame('PIC-A', $row->pic);
    }

    public function test_full_replace_mengosongkan_kolom_yang_tidak_dikirim(): void
    {
        $token = ['Authorization' => 'Bearer '.$this->token];

        $this->withHeaders($token)->postJson('/api/pencairan-platinum/sync', $this->payload([
            ['no_pk' => 'PK-2026-0004', 'nama' => 'Lama', 'pic' => 'PIC-A'],
        ]))->assertOk();

        $this->withHeaders($token)->postJson('/api/pencairan-platinum/sync', $this->payload([
            ['no_pk' => 'PK-2026-0004', 'nama' => 'Baru'],
        ], ['full_replace' => true]))->assertOk();

        $row = DataPencairanPlatinum::where('no_pk', 'PK-2026-0004')->firstOrFail();
        $this->assertSame('Baru', $row->nama);
        $this->assertNull($row->pic);
    }

    public function test_baris_gagal_tidak_menggagalkan_baris_lain(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/pencairan-platinum/sync', $this->payload([
                ['no_pk' => 'PK-OK-1', 'nama' => 'Fine'],
                ['nama' => 'Tanpa No PK'],
                ['no_pk' => 'PK-BAD-TANGGAL', 'tgl_akad' => '31/02/2026'],
                ['no_pk' => 'PK-BAD-ANGKA', 'plafond' => 'sejuta'],
                ['no_pk' => 'PK-OK-2', 'nama' => 'Juga Fine'],
            ]));

        $response->assertOk()->assertJson([
            'success' => true,
            'received' => 5,
            'inserted' => 2,
            'updated' => 0,
            'failed' => 3,
        ]);

        $this->assertSame(2, DataPencairanPlatinum::count());

        $errors = $response->json('errors');
        $this->assertCount(3, $errors);
        $this->assertSame([2, 3, 4], array_column($errors, 'row'));
        $this->assertArrayHasKey('no_pk', $errors[0]['messages']);
        $this->assertArrayHasKey('tgl_akad', $errors[1]['messages']);
        $this->assertArrayHasKey('plafond', $errors[2]['messages']);
    }

    public function test_semua_baris_gagal_mengembalikan_422(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/pencairan-platinum/sync', $this->payload([
                ['nama' => 'Tanpa No PK'],
            ]))
            ->assertStatus(422)
            ->assertJson(['success' => false, 'inserted' => 0, 'failed' => 1]);
    }

    public function test_kolom_tak_dikenal_dilaporkan_tanpa_menggagalkan(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/pencairan-platinum/sync', $this->payload([
                ['no_pk' => 'PK-2026-0005', 'kolom_ngawur' => 'apa saja'],
            ]));

        $response->assertOk()->assertJson(['inserted' => 1]);
        $this->assertSame(['kolom_ngawur'], $response->json('ignored_fields'));
    }

    public function test_tanggal_iso_dari_apps_script_diterima(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/pencairan-platinum/sync', $this->payload([
                ['no_pk' => 'PK-2026-0006', 'tgl_akad' => '2026-03-04T00:00:00.000Z'],
            ]))
            ->assertOk();

        $row = DataPencairanPlatinum::where('no_pk', 'PK-2026-0006')->firstOrFail();
        $this->assertSame('2026-03-04', $row->tgl_akad->format('Y-m-d'));
    }

    public function test_payload_kosong_ditolak(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/pencairan-platinum/sync', ['rows' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors('rows');
    }

    public function test_teks_berlebih_dipotong_sesuai_panjang_kolom(): void
    {
        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/pencairan-platinum/sync', $this->payload([
                ['no_pk' => 'PK-2026-0007', 'nopen' => str_repeat('9', 80)],
            ]))
            ->assertOk();

        $row = DataPencairanPlatinum::where('no_pk', 'PK-2026-0007')->firstOrFail();
        $this->assertSame(50, strlen($row->nopen));
    }
}
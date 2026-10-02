<?php

namespace Tests\Feature;

use App\Models\DataSimulasi;
use App\Models\Debitur;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test halaman Data Pengajuan: list ringkas (2 tombol) + form activity yang
 * memegang semua aksi debitur dan pengajuan.
 *
 * Transaksi dibuat manual setelah database di-pointing ke database dev, bukan
 * lewat trait DatabaseTransactions, karena trait itu sudah berjalan di dalam
 * parent::setUp() yaitu sebelum penggantian database.
 */
class DataPengajuanTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.mysql.database' => 'new_pensiun_kredit']);
        config(['database.default' => 'mysql']);
        DB::purge('mysql');

        DB::connection('mysql')->beginTransaction();

        $this->user = User::query()->where('email', 'admin@nbp.com')->firstOrFail();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql')->rollBack();
        DB::purge('mysql');

        parent::tearDown();
    }

    private function simulasi(array $attributes = []): DataSimulasi
    {
        return DataSimulasi::create(array_merge([
            'nama_debitur' => 'Debitur Uji',
            'nomor_pensiun' => 'UJI-' . uniqid(),
            'status' => 'confirmed',
            'gaji_pensiun' => 5000000,
            'plafond' => 100000000,
            'tenor' => 24,
        ], $attributes));
    }

    // ------------------------------------------------------------------
    // List
    // ------------------------------------------------------------------

    public function test_list_menampilkan_simulasi_confirmed(): void
    {
        $simulasi = $this->simulasi(['nama_debitur' => 'Budi Santoso', 'nomor_pensiun' => 'LIST-0001']);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.index'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('LIST-0001')
            ->assertSee(route('data_pengajuan.info', $simulasi));
    }

    public function test_list_hanya_punya_dua_action_button(): void
    {
        $this->simulasi(['nama_debitur' => 'Cuma Dua Tombol']);

        $html = $this->actingAs($this->user)
            ->get(route('data_pengajuan.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>Info<', $html);
        $this->assertStringContainsString('Download PDF', $html);

        // Aksi yang tadinya memenuhi baris tabel sudah pindah ke form.
        foreach ([
            'Preview DNKA Horizontal',
            'Preview Repayment Schedule',
            'Download Excel Bundle',
            'PK Standard',
            'SI New/Topup',
            'SPPK',
            'Edit Pelengkap',
            'Upload IDPB',
            'Back to Trial',
        ] as $tombolLama) {
            $this->assertStringNotContainsString($tombolLama, $html, 'Tombol "' . $tombolLama . '" seharusnya tidak ada di list.');
        }

        // "Confirm" dicek per tombol, bukan substring polos, supaya tidak
        // ikut cocok dengan badge status "Confirmed".
        $this->assertStringNotContainsString('>Confirm<', $html);
    }

    public function test_list_tidak_menampilkan_trial_secara_default(): void
    {
        $this->simulasi(['nama_debitur' => 'Masih Trial', 'status' => 'trial']);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.index'))
            ->assertOk()
            ->assertDontSee('Masih Trial');
    }

    public function test_filter_status_trial_menampilkan_trial(): void
    {
        $this->simulasi(['nama_debitur' => 'Trial Tampil', 'status' => 'trial']);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.index', ['status' => 'trial']))
            ->assertOk()
            ->assertSee('Trial Tampil');
    }

    public function test_search_memfilter_nopen_dan_nama(): void
    {
        $this->simulasi(['nama_debitur' => 'Kenzo Yamato', 'nomor_pensiun' => 'CARI-1111']);
        $this->simulasi(['nama_debitur' => 'Someone Else', 'nomor_pensiun' => 'LAIN-2222']);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.index', ['nopen' => 'CARI-1111']))
            ->assertOk()
            ->assertSee('Kenzo Yamato')
            ->assertDontSee('Someone Else');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('data_pengajuan.index'))->assertRedirect(route('login'));
    }

    public function test_menu_menyembunyikan_menu_yang_belum_dipakai(): void
    {
        $html = $this->actingAs($this->user)
            ->get(route('data_pengajuan.index'))
            ->assertOk()
            ->getContent();

        // Menu ini disembunyikan sementara, tapi rutenya tetap hidup.
        $this->assertStringNotContainsString('List Banpot', $html);
        $this->assertStringNotContainsString('List Payment', $html);
        $this->assertStringNotContainsString('Import Initial Nominatif', $html);

        // Menu yang aktif harus tetap muncul.
        $this->assertStringContainsString('Data Pengajuan', $html);
        $this->assertStringContainsString('Debitur Info', $html);
    }

    // ------------------------------------------------------------------
    // Form activity
    // ------------------------------------------------------------------

    public function test_form_menampilkan_empat_tab(): void
    {
        $simulasi = $this->simulasi(['nomor_pensiun' => 'FORM-0001']);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.info', $simulasi))
            ->assertOk()
            ->assertSee('Data Debitur')
            ->assertSee('Pengajuan')
            ->assertSee('Dokumen')
            ->assertSee('Surat');
    }

    public function test_form_memuat_seluruh_aksi_yang_tadinya_di_list(): void
    {
        $simulasi = $this->simulasi(['nomor_pensiun' => 'AKSI-0001']);

        $html = $this->actingAs($this->user)
            ->get(route('data_pengajuan.info', $simulasi))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Edit Simulasi', $html);
        $this->assertStringContainsString('Back to Trial', $html);

        // DataSimulasi otomatis membuat record pelengkap, jadi label tombolnya
        // "Edit Pelengkap". "Input Pelengkap" hanya muncul untuk row lama.
        $this->assertMatchesRegularExpression('/Input Pelengkap|Edit Pelengkap/', $html);
        $this->assertStringContainsString(route('data_simulasi.pelengkap.edit', $simulasi), $html);

        $this->assertStringContainsString('Upload IDPB', $html);
        $this->assertStringContainsString('Upload Permohonan CIF', $html);
        $this->assertStringContainsString('Upload Pelunasan TO KB', $html);
        $this->assertStringContainsString('Download Excel Bundle', $html);
        $this->assertStringContainsString('DNKA Horizontal', $html);
        $this->assertStringContainsString('DNKA Vertical', $html);
        $this->assertStringContainsString('Data Nominatif', $html);
        $this->assertStringContainsString('Data LOS Bulk', $html);
        $this->assertStringContainsString('Data Rekening', $html);
        $this->assertStringContainsString('Repayment Schedule', $html);
        $this->assertStringContainsString('PK Standard', $html);
        $this->assertStringContainsString('PK KB Version', $html);
        $this->assertStringContainsString('SI TO', $html);
        $this->assertStringContainsString('SI New/Topup', $html);
        $this->assertStringContainsString('SPPK', $html);
    }

    public function test_form_trial_menampilkan_confirm_dan_hapus(): void
    {
        $simulasi = $this->simulasi(['status' => 'trial', 'nomor_pensiun' => 'TRL-0001']);

        $html = $this->actingAs($this->user)
            ->get(route('data_pengajuan.info', $simulasi))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('>Confirm<', $html);
        $this->assertStringContainsString('Yakin hapus trial simulasi ini?', $html);
        $this->assertStringNotContainsString('Back to Trial', $html);
    }

    public function test_form_menampilkan_ringkasan_nilai_simulasi(): void
    {
        $simulasi = $this->simulasi([
            'nomor_pensiun' => 'RING-0001',
            'nama_debitur' => 'Ringkasan Test',
            'plafond' => 125000000,
            'bank_tujuan' => 'MANDIRI',
        ]);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.info', $simulasi, ['tab' => 'pengajuan']))
            ->assertOk()
            ->assertSee('Ringkasan Test')
            ->assertSee('125.000.000')
            ->assertSee('MANDIRI');
    }

    public function test_form_membuat_profile_debitur_bila_belum_ada(): void
    {
        $simulasi = $this->simulasi(['nomor_pensiun' => 'AUTO-0001', 'nama_debitur' => 'Dari Form']);

        $this->assertNull(Debitur::findByNopen('AUTO-0001'));

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.info', $simulasi))
            ->assertOk()
            ->assertSee('Dari Form');

        $this->assertNotNull(Debitur::findByNopen('AUTO-0001'));
    }

    public function test_form_tanpa_nopen_tidak_membuat_debitur(): void
    {
        $simulasi = $this->simulasi(['nomor_pensiun' => '', 'nama_debitur' => 'Tanpa Nopen']);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.info', $simulasi))
            ->assertOk()
            ->assertSee('belum bisa dibuat');

        $this->assertNull(Debitur::findByNopen(''));
    }

    // ------------------------------------------------------------------
    // Search di dalam form
    // ------------------------------------------------------------------

    public function test_cari_melompat_ke_simulasi_yang_cocok(): void
    {
        $tujuan = $this->simulasi(['nama_debitur' => 'Target Lompat', 'nomor_pensiun' => 'LOM-0001']);
        $this->simulasi(['nama_debitur' => 'Bukan Target', 'nomor_pensiun' => 'LAIN-0002']);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.cari', ['q' => 'LOM-0001']))
            ->assertRedirect(route('data_pengajuan.info', $tujuan));
    }

    public function test_cari_juga_mencari_lewat_tabel_debitur(): void
    {
        $tujuan = $this->simulasi(['nama_debitur' => 'Lompat Via Debitur', 'nomor_pensiun' => 'DEB-0003']);
        Debitur::create([
            'nopen' => 'DEB-0003',
            'nama_ktp' => 'Lompat Via Debitur',
            'nik' => '3201010101800009',
        ]);

        $this->actingAs($this->user)
            ->get(route('data_pengajuan.cari', ['q' => '3201010101800009']))
            ->assertRedirect(route('data_pengajuan.info', $tujuan));
    }

    public function test_cari_tidak_ada_hasil_kembali_ke_list_dengan_pesan(): void
    {
        $this->actingAs($this->user)
            ->get(route('data_pengajuan.cari', ['q' => 'TIDAK-ADA-9999']))
            ->assertRedirect(route('data_pengajuan.index'))
            ->assertSessionHas('error');
    }

    public function test_cari_kosong_kembali_ke_list(): void
    {
        $this->actingAs($this->user)
            ->get(route('data_pengajuan.cari', ['q' => '   ']))
            ->assertRedirect(route('data_pengajuan.index'));
    }

    // ------------------------------------------------------------------
    // Simpan data debitur dari form pengajuan
    // ------------------------------------------------------------------

    public function test_simpan_debitur_dari_form_pengajuan_kembali_ke_form(): void
    {
        $simulasi = $this->simulasi(['nomor_pensiun' => 'SAVE-0001']);
        $debitur = Debitur::untukSimulasi($simulasi);

        $this->actingAs($this->user)->put(
            route('debitur.update', ['nopen' => $debitur->nopen]),
            [
                'nama_ktp' => 'Nama Dari Form Pengajuan',
                'return_to' => 'data_pengajuan.info',
            ]
        )->assertRedirect(route('data_pengajuan.info', $simulasi))
         ->assertSessionHas('success');

        $this->assertSame('Nama Dari Form Pengajuan', $debitur->refresh()->nama_ktp);
    }

    public function test_return_to_asing_tidak_membuka_redirect_luar(): void
    {
        $simulasi = $this->simulasi(['nomor_pensiun' => 'SAVE-0002']);
        $debitur = Debitur::untukSimulasi($simulasi);

        $response = $this->actingAs($this->user)->put(
            route('debitur.update', ['nopen' => $debitur->nopen]),
            [
                'nama_ktp' => 'Aman',
                'return_to' => 'https://evil.example.com',
            ]
        );

        $response->assertRedirect(route('debitur.edit', ['nopen' => $debitur->nopen]));
    }

    public function test_validasi_gagal_kembali_ke_form_pengajuan_bukan_ke_halaman_debitur(): void
    {
        $simulasi = $this->simulasi(['nomor_pensiun' => 'ERR-0001']);
        $debitur = Debitur::untukSimulasi($simulasi);

        $this->actingAs($this->user)
            ->from(route('data_pengajuan.info', $simulasi))
            ->put(route('debitur.update', ['nopen' => $debitur->nopen]), [
                // Melebihi aturan maxlength 75.
                'nama_ktp' => str_repeat('A', 100),
                'return_to' => 'data_pengajuan.info',
            ])
            ->assertRedirect(route('data_pengajuan.info', $simulasi))
            ->assertSessionHasErrors('nama_ktp');

        // Nilai lama tidak boleh tertimpa oleh request yang gagal validasi.
        $this->assertNotSame(str_repeat('A', 100), $debitur->refresh()->nama_ktp);
    }

    // ------------------------------------------------------------------
    // Smoke test terhadap data yang benar-benar ada di database
    // ------------------------------------------------------------------

    public function test_form_tampil_utuh_untuk_seluruh_row_yang_ada_di_database(): void
    {
        $semua = DataSimulasi::query()
            ->whereNotNull('nomor_pensiun')
            ->where('nomor_pensiun', '!=', '')
            ->orderBy('id')
            ->limit(40)
            ->get();

        if ($semua->isEmpty()) {
            $this->markTestSkipped('Tidak ada data simulasi di database dev.');
        }

        foreach ($semua as $simulasi) {
            $this->actingAs($this->user)
                ->get(route('data_pengajuan.info', $simulasi))
                ->assertOk();
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Agama;
use App\Models\DataSimulasi;
use App\Models\Debitur;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;
use App\Models\StatusKawin;
use App\Models\StatusRumah;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Test Debitur Info.
 *
 * Sengaja memakai transaksi manual (bukan `RefreshDatabase`) supaya jalan di
 * database yang sudah termigrasi penuh â€” tabel referensi wilayah berisi 82rb
 * baris kelurahan yang tidak perlu dibuat ulang tiap test run.
 */
class DebiturInfoTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // phpunit.xml default-nya mengarah ke database test lama; pakai database dev.
        config(['database.connections.mysql.database' => 'new_pensiun_kredit']);
        config(['database.default' => 'mysql']);
        DB::purge('mysql');

        // Transaksi baru dimulai setelah connection di-pointing ke database dev.
        // Trait DatabaseTransactions tidak bisa dipakai karena transaksinya sudah
        // jalan di dalam parent::setUp(), yaitu sebelum baris config di atas.
        DB::connection('mysql')->beginTransaction();

        $this->user = User::query()->where('email', 'admin@nbp.com')->firstOrFail();

        $rc = new \ReflectionClass(\App\Models\Wilayah::class);
    }

    protected function tearDown(): void
    {
        DB::connection('mysql')->rollBack();
        DB::purge('mysql');

        parent::tearDown();
    }

    private function debitur(array $attributes = []): Debitur
    {
        return Debitur::create(array_merge([
            'nopen' => 'NOPEN-' . uniqid(),
            'nama_ktp' => 'Debitur Uji',
        ], $attributes));
    }

    // ------------------------------------------------------------------
    // List + search
    // ------------------------------------------------------------------

    public function test_list_menampilkan_semua_debitur(): void
    {
        $debitur = $this->debitur(['nopen' => 'LIST-0001', 'nama_ktp' => 'Budi Santoso']);

        $this->actingAs($this->user)
            ->get(route('debitur.index'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee($debitur->nopen);
    }

    public function test_search_memfilter_berdasarkan_nopen_dan_nama(): void
    {
        $this->debitur(['nopen' => 'CARI-1111', 'nama_ktp' => 'Kenzo Yamato']);
        $this->debitur(['nopen' => 'LAIN-2222', 'nama_ktp' => 'Someone Else']);

        $this->actingAs($this->user)
            ->get(route('debitur.index', ['nopen' => 'CARI-1111']))
            ->assertOk()
            ->assertSee('Kenzo Yamato')
            ->assertDontSee('Someone Else');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('debitur.index'))->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------
    // Form bertab
    // ------------------------------------------------------------------

    public function test_form_edit_menampilkan_tiga_tab_dan_dropdown_referensi(): void
    {
        $debitur = $this->debitur();

        $response = $this->actingAs($this->user)->get(route('debitur.edit', ['nopen' => $debitur->nopen]));

        $response->assertOk();
        $response->assertSee('Data Pribadi & Pensiun');
        $response->assertSee('Alamat Rumah & Domisili');
        $response->assertSee('Keluarga & Alamat Pasangan');

        // Opsi dari tabel referensi ikut ter-render.
        $response->assertSee('Islam');
        $response->assertSee('MILIK SENDIRI');
        $response->assertSee('TASPEN');
        $response->assertSee('ASABRI');
        $response->assertSee('ACEH');
    }

    public function test_form_edit_membuat_baris_debitur_dari_simulasi_bila_belum_ada(): void
    {
        $simulasi = DataSimulasi::create([
            'nama_debitur' => 'Dari Simulasi',
            'nomor_pensiun' => 'AUTO-0001',
            'tanggal_lahir' => '1965-03-04',
            'nomor_hp' => '08123456789',
            'gaji_pensiun' => 4500000,
            'status' => 'trial',
        ]);

        $this->assertNull(Debitur::findByNopen('AUTO-0001'));

        $this->actingAs($this->user)
            ->get(route('debitur.edit', ['nopen' => 'AUTO-0001']))
            ->assertOk()
            ->assertSee('Dari Simulasi');

        $debitur = Debitur::findByNopen('AUTO-0001');
        $this->assertNotNull($debitur, 'Baris debitur harus dibuat otomatis dari simulasi.');
        $this->assertSame('Dari Simulasi', $debitur->nama_ktp);
        $this->assertSame('08123456789', $debitur->hp);
        $this->assertEquals(4500000.0, $debitur->gaji);
    }

    public function test_form_edit_404_bila_nopen_tidak_dikenal(): void
    {
        $this->actingAs($this->user)
            ->get(route('debitur.edit', ['nopen' => 'TIDAK-ADA-9999']))
            ->assertNotFound();

        $this->assertNull(Debitur::findByNopen('TIDAK-ADA-9999'));
    }

    // ------------------------------------------------------------------
    // Simpan
    // ------------------------------------------------------------------

    public function test_update_menyimpan_field_form_dan_mencerminkan_kolom_kode_warisan(): void
    {
        $debitur = $this->debitur();


        $provinsi = Wilayah::opsi(Wilayah::PROVINSI)[0]['code'];

        $kabupaten = Wilayah::opsi(Wilayah::KABUPATEN, $provinsi)[0]['code'];
        $kecamatan = Wilayah::opsi(Wilayah::KECAMATAN, $kabupaten)[0]['code'];
        $kelurahan = Wilayah::opsi(Wilayah::KELURAHAN, $kecamatan)[0]['code'];


        // Nilai dropdown diambil dari tabel referensi, bukan ditulis manual di
        // sini, supaya test tetap valid kalau data referensinya berubah.
        $agama = array_values(Agama::opsi())[0];
        $statusKawin = array_values(StatusKawin::opsi())[0];
        $pendidikan = array_values(Pendidikan::opsi())[0];
        $pekerjaan = array_values(Pekerjaan::opsi())[0];
        $statusRumah = array_values(StatusRumah::opsi())[0];

        $response = $this->actingAs($this->user)->put(
            route('debitur.update', ['nopen' => $debitur->nopen]),
            [
                'nama_ktp' => 'Nama Baru',
                'nik' => '3201010101800001',
                'agama' => $agama,
                'status_pernikahan' => $statusKawin,
                'pendidikan' => $pendidikan,
                'pekerjaan' => $pekerjaan,
                'status_rumah' => $statusRumah,
                'instansi_pensiun' => 'TASPEN',
                'jenis_kelamin' => 'L',
                'tgl_lahir' => '1960-01-02',
                'gaji' => '5000000',
                'prop_rumah' => $provinsi,
                'kota_rumah' => $kabupaten,
                'kec_rumah' => $kecamatan,
                'kel_rumah' => $kelurahan,
            ]
        );

        $response->assertRedirect(route('debitur.edit', ['nopen' => $debitur->nopen]));
        $response->assertSessionHas('success');

        $debitur->refresh();

        $this->assertSame('Nama Baru', $debitur->nama_ktp);
        $this->assertSame('3201010101800001', $debitur->nik);
        $this->assertSame($agama, $debitur->agama);
        $this->assertSame($statusKawin, $debitur->status_pernikahan);
        $this->assertSame($pendidikan, $debitur->pendidikan);
        $this->assertSame($pekerjaan, $debitur->pekerjaan);
        $this->assertSame($statusRumah, $debitur->status_rumah);
        $this->assertSame('TASPEN', $debitur->instansi_pensiun);
        $this->assertSame('1960-01-02', $debitur->tgl_lahir->format('Y-m-d'));
        $this->assertEquals(5000000.0, $debitur->gaji);


        // Kolom wilayah tersimpan sebagai kode BPS.
        $this->assertSame($provinsi, $debitur->prop_rumah);
        $this->assertSame($kelurahan, $debitur->kel_rumah);

        // Kolom `*_code` warisan ikut tercerminkan.
        $this->assertSame($provinsi, $debitur->propinsi_code);
        $this->assertSame($kabupaten, $debitur->kota_code);
        $this->assertSame($kecamatan, $debitur->kec_code);
        $this->assertSame($kelurahan, $debitur->kel_code);
    }

    public function test_update_menolak_kode_wilayah_yang_tidak_terdaftar(): void
    {
        $debitur = $this->debitur();

        $this->actingAs($this->user)
            ->put(route('debitur.update', ['nopen' => $debitur->nopen]), [
                'nama_ktp' => 'X',
                'prop_rumah' => '999999',
            ])
            ->assertSessionHasErrors('prop_rumah');
    }

    public function test_update_menolak_nilai_dropdown_di_luar_referensi(): void
    {
        $debitur = $this->debitur();

        $this->actingAs($this->user)
            ->put(route('debitur.update', ['nopen' => $debitur->nopen]), [
                'nama_ktp' => 'X',
                'agama' => 'Agama Ngawur',
            ])
            ->assertSessionHasErrors('agama');
    }

    public function test_update_mengabaikan_upaya_mengganti_nopen(): void
    {
        $debitur = $this->debitur();
        $nopenAwal = $debitur->nopen;

        $this->actingAs($this->user)->put(
            route('debitur.update', ['nopen' => $nopenAwal]),
            ['nama_ktp' => 'Nama Tetap', 'nopen' => 'NOPEN-DIHALANGI']
        );

        $debitur->refresh();

        $this->assertSame($nopenAwal, $debitur->nopen, 'nopen tidak boleh berubah lewat form.');
        $this->assertSame('Nama Tetap', $debitur->nama_ktp);
        $this->assertNull(Debitur::findByNopen('NOPEN-DIHALANGI'));
    }

    // ------------------------------------------------------------------
    // Endpoint cascade wilayah
    // ------------------------------------------------------------------

    public function test_endpoint_wilayah_mengembalikan_anak_dari_parent(): void
    {
        $provinsi = Wilayah::opsi(Wilayah::PROVINSI)[0]['code'];

        $expected = Wilayah::opsi(Wilayah::KABUPATEN, $provinsi);

        $this->actingAs($this->user)
            ->getJson(route('debitur.wilayah', ['tingkat' => 'kabupaten']) . '?parent=' . $provinsi)
            ->assertOk()
            ->assertJsonCount(count($expected), 'options')
            ->assertJsonPath('options.0.name', $expected[0]['name']);
    }

    public function test_endpoint_wilayah_menolak_tingkat_tidak_dikenal(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('debitur.wilayah', ['tingkat' => 'drop_table']))
            ->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Auto-insert saat simulasi di-confirm
    // ------------------------------------------------------------------

    public function test_confirm_simulasi_membuat_profile_debitur(): void
    {
        $simulasi = DataSimulasi::create([
            'nama_debitur' => 'Konfirmasi Test',
            'nomor_pensiun' => 'KONF-0001',
            'tanggal_lahir' => '1962-05-06',
            'gaji_pensiun' => 6000000,
            'status' => 'trial',
        ]);

        $this->assertNull(Debitur::findByNopen('KONF-0001'));

        $this->actingAs($this->user)
            ->patch(route('data_simulasi.confirm', $simulasi))
            ->assertRedirect(route('data_simulasi.trial.list'));

        $debitur = Debitur::findByNopen('KONF-0001');
        $this->assertNotNull($debitur);
        $this->assertSame('Konfirmasi Test', $debitur->nama_ktp);
        $this->assertEquals(6000000.0, $debitur->gaji);
    }

    public function test_confirm_simulasi_tidak_menimpa_debitur_yang_sudah_ada(): void
    {
        $existing = $this->debitur([
            'nopen' => 'KONF-0002',
            'nama_ktp' => 'Nama Sudah Diisi User',
            'agama' => 'Kristen',
        ]);

        $simulasi = DataSimulasi::create([
            'nama_debitur' => 'Harus Tidak Mengganti',
            'nomor_pensiun' => 'KONF-0002',
            'status' => 'trial',
        ]);

        $this->actingAs($this->user)->patch(route('data_simulasi.confirm', $simulasi));

        $existing->refresh();
        $this->assertSame('Nama Sudah Diisi User', $existing->nama_ktp);
        $this->assertSame('Kristen', $existing->agama);
        $this->assertSame(1, Debitur::where('nopen', 'KONF-0002')->count());
    }

    public function test_confirm_simulasi_tanpa_nopen_tidak_membuat_debitur(): void
    {
        $simulasi = DataSimulasi::create([
            'nama_debitur' => 'Tanpa Nopen',
            'nomor_pensiun' => '',
            'status' => 'trial',
        ]);

        $this->actingAs($this->user)->patch(route('data_simulasi.confirm', $simulasi));

        $this->assertSame(0, Debitur::whereNull('nopen')->count());
    }
}

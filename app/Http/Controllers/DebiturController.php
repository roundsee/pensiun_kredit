<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DefinesDebiturForm;
use App\Models\DataSimulasi;
use App\Models\Debitur;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DebiturController extends Controller
{
    use DefinesDebiturForm;

    public function index(Request $request)
    {
        $filters = [
            'nopen' => trim((string) $request->query('nopen', '')),
            'nama' => trim((string) $request->query('nama', '')),
            'nik' => trim((string) $request->query('nik', '')),
            'hp' => trim((string) $request->query('hp', '')),
        ];

        $debitur = Debitur::query()
            ->when($filters['nopen'] !== '', fn ($query) => $query->where('nopen', 'like', '%' . $filters['nopen'] . '%'))
            ->when($filters['nama'] !== '', fn ($query) => $query->where('nama_ktp', 'like', '%' . $filters['nama'] . '%'))
            ->when($filters['nik'] !== '', fn ($query) => $query->where('nik', 'like', '%' . $filters['nik'] . '%'))
            ->when($filters['hp'] !== '', fn ($query) => $query->where('hp', 'like', '%' . $filters['hp'] . '%'))
            ->orderByDesc('id')
            ->paginate(config('debitur.per_page', 15))
            ->appends($filters);

        return view('debitur.index', compact('debitur', 'filters'));
    }

    public function edit(Request $request, string $nopen)
    {
        $debitur = $this->cariAtauBuat($nopen);

        return view('debitur.form', [
            'debitur' => $debitur,
            'tabs' => $this->debiturTabs(),
            'fieldMap' => $this->debiturFieldMap(),
        ]);
    }

    public function update(Request $request, string $nopen)
    {
        $debitur = $this->cariAtauBuat($nopen);

        $validated = $request->validate($this->rules(), $this->messages(), $this->attributes());

        // `nopen` adalah kunci baris dan jadi bagian dari URL, jadi tidak boleh
        // diubah lewat form ini.
        unset($validated['nopen']);

        $debitur->fill($this->sinkronkanKodeWilayah($validated));
        $debitur->save();

        return redirect()
            ->to($this->returnUrl($request, $debitur->nopen))
            ->with('success', 'Data debitur berhasil disimpan.');
    }

    /**
     * Halaman tujuan setelah simpan. Form debitur dipakai di dua tempat —
     * halaman Debitur Info dan form activity pengajuan — jadi tujuan kiranya
     * dikirim lewat input `return_to`. Nilainya dicocokkan ke daftar putih
     * route internal, bukan dipakai langsung sebagai URL.
     */
    private function returnUrl(Request $request, string $nopen): string
    {
        if ((string) $request->input('return_to') === 'data_pengajuan.info') {
            $simulasi = DataSimulasi::query()
                ->where('nomor_pensiun', $nopen)
                ->latest('id')
                ->first();

            if ($simulasi !== null) {
                return route('data_pengajuan.info', $simulasi);
            }
        }

        return route('debitur.edit', ['nopen' => $nopen]);
    }

    /**
     * Cari profile debitur berdasarkan nopen. Kalau belum ada, buat dari simulasi
     * terbaru dengan nopen tersebut supaya form yang dibuka dari list simulasi
     * tidak pernah leading ke halaman kosong. Kalau tidak ada simulasi maupun
     * profile, berarti nopen-nya memang tidak dikenal.
     */
    private function cariAtauBuat(string $nopen): Debitur
    {
        $nopen = trim($nopen);
        abort_if($nopen === '', 404);

        $debitur = Debitur::findByNopen($nopen);

        if ($debitur !== null) {
            return $debitur;
        }

        $simulasi = DataSimulasi::query()
            ->where('nomor_pensiun', $nopen)
            ->latest('id')
            ->first();

        abort_if($simulasi === null, 404);

        $debitur = Debitur::untukSimulasi($simulasi);
        abort_if($debitur === null, 404);

        return $debitur;
    }

    /**
     * Opsi wilayah untuk cascade dropdown.
     */
    public function wilayah(Request $request, string $tingkat)
    {
        abort_unless(Wilayah::tingkatValid($tingkat), 404);

        return response()->json([
            'options' => Wilayah::opsi(
                $tingkat,
                $request->query('parent'),
                $request->query('q')
            ),
        ]);
    }

    /**
     * Aturan validasi, diturunkan dari definisi field supaya tidak ada field
     * yang lolos ke database tanpa kolomnya terdaftar di `$fillable`.
     */
    private function rules(): array
    {
        $rules = [];

        foreach ($this->debiturFieldMap() as $name => $field) {
            $rules[$name] = [$this->wajib($field) ? 'required' : 'nullable'];

            if ($field['type'] === 'date') {
                $rules[$name][] = 'date';
            } elseif ($field['type'] === 'number') {
                $rules[$name][] = 'numeric';
            } elseif ($field['type'] === 'text') {
                $rules[$name][] = 'string';
                $rules[$name][] = 'max:' . ($field['maxlength'] ?? 255);
            } elseif ($field['type'] === 'textarea') {
                $rules[$name][] = 'string';
                $rules[$name][] = 'max:' . ($field['maxlength'] ?? 255);
            } elseif ($field['type'] === 'select') {
                $rules[$name][] = 'string';

                if (isset($field['wilayah'])) {
                    $rules[$name][] = Rule::exists($field['wilayah'], 'code');
                } else {
                    $rules[$name][] = Rule::in(array_keys($field['options'] ?? []));
                }
            }
        }

        return $rules;
    }

    private function wajib(array $field): bool
    {
        return (bool) ($field['required'] ?? false);
    }

    private function messages(): array
    {
        $messages = [];

        foreach ($this->debiturFieldMap() as $name => $field) {
            $label = $field['label'];

            $messages[$name . '.required'] = $label . ' wajib diisi.';
            $messages[$name . '.exists'] = $label . ' tidak terdaftar pada referensi wilayah.';
            $messages[$name . '.in'] = $label . ' tidak valid.';
            $messages[$name . '.date'] = $label . ' tidak valid.';
            $messages[$name . '.max'] = $label . ' maksimal :max karakter.';
        }

        return $messages;
    }

    private function attributes(): array
    {
        $attributes = [];

        foreach ($this->debiturFieldMap() as $name => $field) {
            $attributes[$name] = $field['label'];
        }

        return $attributes;
    }

    /**
     * Kolom `*_code` warisan dicerminkan dari kolom wilayah utama supaya baris
     * tetap konsisten untuk konsumen lama yang membaca kedua bentuk itu.
     */
    private function sinkronkanKodeWilayah(array $validated): array
    {
        $pasangan = [
            'prop_rumah' => 'propinsi_code',
            'kota_rumah' => 'kota_code',
            'kec_rumah' => 'kec_code',
            'kel_rumah' => 'kel_code',
            'prop_domilsili' => 'propinsi_domisili_code',
            'kota_domiisili' => 'kota_domisili_code',
            'kec_domisili' => 'kec_domisili_code',
            'kel_domisili' => 'kel_domisili_code',
        ];

        foreach ($pasangan as $sumber => $tujuan) {
            if (array_key_exists($sumber, $validated)) {
                $validated[$tujuan] = $validated[$sumber];
            }
        }

        return $validated;
    }
}

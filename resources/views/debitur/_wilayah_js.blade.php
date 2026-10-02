{{-- Logika cascade dropdown wilayah + lompat ke tab yang bermasalah.
     Dipakai bersama oleh halaman Debitur Info dan form activity pengajuan.
     Guard di bawah mencegah inisialisasi dua kali kalau partial ikut termuat
     di dua form pada satu halaman. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.__debiturFormInit) {
            return;
        }
        window.__debiturFormInit = true;

        const wilayahUrl = @json(route('debitur.wilayah', ['tingkat' => '__TIKAT__']));

        async function ambilOpsi(tier, parentCode) {
            const url = new URL(wilayahUrl.replace('__TIKAT__', tier), window.location.origin);
            if (parentCode) {
                url.searchParams.set('parent', parentCode);
            }

            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) {
                    return [];
                }
                const payload = await response.json();
                return Array.isArray(payload.options) ? payload.options : [];
            } catch (error) {
                return [];
            }
        }

        function isiOpsi(select, options, pertahankanNilai) {
            const nilaiSebelumnya = pertahankanNilai ? select.value : '';

            select.replaceChildren(new Option('-- Pilih --', ''));

            let masihAda = false;
            options.forEach(function (option) {
                select.appendChild(new Option(option.name, option.code));
                if (String(option.code) === String(nilaiSebelumnya)) {
                    masihAda = true;
                }
            });

            select.value = masihAda ? nilaiSebelumnya : '';
        }

        // Isi rantai dropdown dari indeks tertentu. Indeks 0 (provinsi) sudah
        // dirender server, jadi hydrate selalu mulai dari indeks 1.
        async function isiRantai(rantai, mulaiDari, pertahankanNilai) {
            for (let i = mulaiDari; i < rantai.length; i++) {
                const parent = rantai[i - 1];
                const nilaiParent = parent ? parent.value : '';

                if (parent && !nilaiParent) {
                    isiOpsi(rantai[i], [], false);
                    continue;
                }

                const options = await ambilOpsi(rantai[i].dataset.wilayahTier, nilaiParent);
                isiOpsi(rantai[i], options, pertahankanNilai);
            }
        }

        // Kelompokkan dropdown wilayah sesuai urutan kemunculannya di form,
        // yaitu provinsi -> kabupaten -> kecamatan -> kelurahan.
        const rantaiWilayah = {};
        document.querySelectorAll('select[data-wilayah-group]').forEach(function (select) {
            const group = select.dataset.wilayahGroup;
            if (!rantaiWilayah[group]) {
                rantaiWilayah[group] = [];
            }
            rantaiWilayah[group].push(select);
        });

        Object.values(rantaiWilayah).forEach(function (rantai) {
            // Tampilkan kembali wilayah yang tersimpan saat form dibuka.
            isiRantai(rantai, 1, true);

            rantai.forEach(function (select, index) {
                select.addEventListener('change', function () {
                    // Nilai anak tidak lagi relevan karena parent berubah.
                    isiRantai(rantai, index + 1, false);
                });
            });
        });

        // Aktifkan tab yang memuat field pertama yang gagal validasi. Field bisa
        // berada di tab bersarang, jadi semua pane leluhur ikut diaktifkan dari
        // yang paling luar ke yang paling dalam.
        const fieldError = document.querySelector('.is-invalid');
        if (!fieldError) {
            return;
        }

        const panes = [];
        let pane = fieldError.closest('.tab-pane');
        while (pane) {
            panes.unshift(pane);
            pane = pane.parentElement ? pane.parentElement.closest('.tab-pane') : null;
        }

        panes.forEach(function (target) {
            const tabButton = document.querySelector('[data-bs-target="#' + target.id + '"]');
            if (tabButton) {
                bootstrap.Tab.getOrCreateInstance(tabButton).show();
            }
        });
    });
</script>

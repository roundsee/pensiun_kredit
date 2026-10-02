{{-- Nav + pane tab data debitur. Dipakai bersama oleh halaman Debitur Info dan
     form activity pengajuan, jadi bentuk tabnya hanya punya satu definisi. --}}
@php
    $debiturTabsId = $debiturTabsId ?? 'debitur-tabs';
    $debiturAktif = $debiturTabAktif ?? ($debiturTabs[0]['id'] ?? null);
@endphp

<ul class="nav nav-tabs mb-3" id="{{ $debiturTabsId }}" role="tablist">
    @foreach($debiturTabs as $tab)
        @php $isAktif = $tab['id'] === $debiturAktif; @endphp
        <li class="nav-item" role="presentation">
            <button
                class="nav-link {{ $isAktif ? 'active' : '' }}"
                id="{{ $debiturTabsId }}-{{ $tab['id'] }}"
                data-bs-toggle="tab"
                data-bs-target="#{{ $debiturTabsId }}-pane-{{ $tab['id'] }}"
                type="button"
                role="tab"
                aria-controls="{{ $debiturTabsId }}-pane-{{ $tab['id'] }}"
                aria-selected="{{ $isAktif ? 'true' : 'false' }}"
            >{{ $tab['title'] }}</button>
        </li>
    @endforeach
</ul>

<div class="tab-content" id="{{ $debiturTabsId }}-content">
    @foreach($debiturTabs as $tab)
        @php $isAktif = $tab['id'] === $debiturAktif; @endphp
        <div
            class="tab-pane fade {{ $isAktif ? 'show active' : '' }}"
            id="{{ $debiturTabsId }}-pane-{{ $tab['id'] }}"
            role="tabpanel"
            aria-labelledby="{{ $debiturTabsId }}-{{ $tab['id'] }}"
        >
            @foreach($tab['groups'] as $group)
                <div class="card mb-3">
                    <div class="card-header bg-light">{{ $group['legend'] }}</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($group['fields'] as $field)
                                @include('debitur._field', ['field' => $debiturFieldMap[$field['name']]])
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>

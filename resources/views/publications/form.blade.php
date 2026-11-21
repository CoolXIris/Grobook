@extends('layouts.app')

@php($inputClass = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500')
@php($isEdit = $publication !== null)

@section('title', $isEdit ? 'Perbaiki Naskah' : 'Pengajuan Publikasi '.$type->label())

@section('content')
    <div class="mx-auto max-w-3xl">
        @if ($isEdit && $openRevision)
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-5">
                <h2 class="font-semibold text-red-800">Catatan Revisi dari {{ $openRevision->requester->name }} ({{ $openRevision->stage->label() }})</h2>
                <p class="mt-1 text-xs text-red-500">{{ $openRevision->created_at->format('d/m/Y H:i') }} WIB</p>
                <p class="mt-2 whitespace-pre-line text-sm text-red-900">{{ $openRevision->comment }}</p>
                @if ($openRevision->file_path)
                    <a href="{{ route('files.revision', $openRevision) }}" target="_blank" class="mt-2 inline-block text-sm font-semibold text-red-700 underline">Unduh file detail revisi (PDF)</a>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ $isEdit ? route('publications.update', $publication) : route('publications.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @else
                <input type="hidden" name="type" value="{{ $type->value }}">
            @endif

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="mb-4 font-semibold">Metadata Publikasi {{ $type->label() }}</h2>

                <div class="grid gap-4">
                    @if ($type === \App\Enums\PublicationType::Arc)
                        <x-field name="arc_publication_id" label="Judul Publikasi (ARC)" required hint="Judul publikasi ARC diatur oleh Petugas DLS sesuai tim Anda.">
                            <select name="arc_publication_id" id="arc_publication_id" class="{{ $inputClass }}" required>
                                <option value="">— Pilih judul publikasi —</option>
                                @foreach ($arcTitles as $arc)
                                    <option value="{{ $arc->id }}" data-title-en="{{ $arc->title_en }}" @selected(old('arc_publication_id', $publication?->arc_publication_id) == $arc->id)>{{ $arc->title }}</option>
                                @endforeach
                            </select>
                            @if ($arcTitles->isEmpty())
                                <p class="mt-1.5 text-xs text-amber-700">Belum ada judul publikasi ARC yang di-assign ke tim Anda. Hubungi Petugas DLS.</p>
                            @endif
                        </x-field>
                        <x-field name="title_en_preview" label="Judul Inggris (otomatis)">
                            <input type="text" id="title_en_preview" class="{{ $inputClass }} bg-slate-50" readonly placeholder="Mengikuti judul yang dipilih">
                        </x-field>
                        <x-field name="frequency" label="Frekuensi Terbit" required>
                            <select name="frequency" class="{{ $inputClass }}" required>
                                <option value="">— Pilih frekuensi —</option>
                                @foreach (\App\Enums\Frequency::cases() as $frequency)
                                    <option value="{{ $frequency->value }}" @selected(old('frequency', $publication?->frequency?->value) === $frequency->value)>{{ $frequency->label() }}</option>
                                @endforeach
                            </select>
                        </x-field>
                        <x-field name="language" label="Bahasa" required>
                            <select name="language" class="{{ $inputClass }}" required>
                                <option value="">— Pilih bahasa —</option>
                                @foreach (\App\Enums\Language::cases() as $language)
                                    <option value="{{ $language->value }}" @selected(old('language', $publication?->language?->value) === $language->value)>{{ $language->label() }}</option>
                                @endforeach
                            </select>
                        </x-field>
                        <x-field name="release_date" label="Jadwal Rilis" required hint="Format: mm/dd/yyyy (contoh: 08/17/2026)">
                            <input type="text" name="release_date" id="release_date" class="{{ $inputClass }}" placeholder="mm/dd/yyyy" value="{{ old('release_date', $publication?->release_date?->format('m/d/Y')) }}" required>
                        </x-field>
                    @else
                        @php($activeTeam = $publication?->team ?? auth()->user()->team)
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-field name="team_name_preview" label="Tim Pembuat Naskah (Otomatis)">
                                <input type="text" id="team_name_preview" class="{{ $inputClass }} bg-slate-50 text-slate-600" value="{{ $activeTeam?->name ?? '-' }}" readonly>
                            </x-field>
                            <x-field name="team_code_preview" label="Kode Tim (Otomatis)">
                                <input type="text" id="team_code_preview" class="{{ $inputClass }} bg-slate-50 font-mono text-slate-600" value="{{ $activeTeam?->code ?? '-' }}" readonly>
                            </x-field>
                        </div>
                        <x-field name="year" label="Tahun Terbit" required>
                            <input type="number" name="year" id="year" class="{{ $inputClass }}" min="1900" max="2100" value="{{ old('year', $publication?->year ?? date('Y')) }}" required>
                        </x-field>
                        <x-field name="title" label="Judul" required>
                            <input type="text" name="title" id="title" class="{{ $inputClass }}" value="{{ old('title', $publication?->title) }}" required>
                        </x-field>
                        <x-field name="title_en" label="Judul Inggris">
                            <input type="text" name="title_en" id="title_en" class="{{ $inputClass }}" value="{{ old('title_en', $publication?->title_en) }}">
                        </x-field>
                        <x-field name="release_date" label="Estimasi Rilis" required hint="Format: mm/dd/yyyy (contoh: 08/17/2026)">
                            <input type="text" name="release_date" id="release_date" class="{{ $inputClass }}" placeholder="mm/dd/yyyy" value="{{ old('release_date', $publication?->release_date?->format('m/d/Y')) }}" required>
                        </x-field>
                        <x-field name="language" label="Bahasa" required>
                            <select name="language" class="{{ $inputClass }}" required>
                                <option value="">— Pilih bahasa —</option>
                                @foreach (\App\Enums\Language::cases() as $language)
                                    <option value="{{ $language->value }}" @selected(old('language', $publication?->language?->value) === $language->value)>{{ $language->label() }}</option>
                                @endforeach
                            </select>
                        </x-field>
                        <x-field name="frequency" label="Frekuensi Terbit" required>
                            <select name="frequency" class="{{ $inputClass }}" required>
                                <option value="">— Pilih frekuensi —</option>
                                @foreach (\App\Enums\Frequency::cases() as $frequency)
                                    <option value="{{ $frequency->value }}" @selected(old('frequency', $publication?->frequency?->value) === $frequency->value)>{{ $frequency->label() }}</option>
                                @endforeach
                            </select>
                        </x-field>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @php($rawRomawi = old('pages_romawi', $publication?->pages_romawi))
                            @php($displayRomawi = is_numeric($rawRomawi) ? \App\Support\RomanNumeral::fromInt((int) $rawRomawi) : (string) $rawRomawi)
                            @php($romanOptions = \App\Support\RomanNumeral::evenUpTo100())
                            <x-field name="pages_romawi" label="Jumlah Halaman Romawi" required hint="Ketik angka romawi genap (II s.d. C) untuk memilih.">
                                <div class="relative" id="romawi-combobox">
                                    <input type="text"
                                           name="pages_romawi"
                                           id="pages_romawi"
                                           class="{{ $inputClass }}"
                                           value="{{ $displayRomawi }}"
                                           placeholder="Ketik mis. II, IV, VIII, X..."
                                           autocomplete="off"
                                           role="combobox"
                                           aria-autocomplete="list"
                                           aria-expanded="false"
                                           aria-controls="romawi-listbox"
                                           required>
                                    <ul id="romawi-listbox"
                                        role="listbox"
                                        class="absolute z-20 mt-1 hidden max-h-52 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-lg">
                                        @foreach ($romanOptions as $num => $roman)
                                            <li role="option"
                                                data-roman="{{ $roman }}"
                                                data-number="{{ $num }}"
                                                class="romawi-option flex cursor-pointer items-center justify-between px-3 py-1.5 text-slate-700 hover:bg-blue-50 hover:text-blue-800">
                                                <span class="font-semibold">{{ $roman }}</span>
                                                <span class="text-xs text-slate-400">{{ $num }} halaman</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </x-field>
                            <x-field name="pages_arabic" label="Jumlah Halaman Arab" required>
                                <input type="number" name="pages_arabic" id="pages_arabic" class="{{ $inputClass }}" min="1" value="{{ old('pages_arabic', $publication?->pages_arabic) }}" required>
                            </x-field>
                        </div>
                        <x-field name="has_cooperation" label="Kerjasama dengan Instansi Luar?" required>
                            <div class="flex gap-6 pt-1 text-sm">
                                <label class="flex items-center gap-2"><input type="radio" name="has_cooperation" value="1" @checked(old('has_cooperation', $publication?->has_cooperation ? '1' : '0') === '1') required> Ya</label>
                                <label class="flex items-center gap-2"><input type="radio" name="has_cooperation" value="0" @checked(old('has_cooperation', $publication?->has_cooperation ? '1' : '0') === '0')> Tidak</label>
                            </div>
                        </x-field>
                        <x-field name="published_for" label="Diterbitkan untuk" required>
                            <select name="published_for" class="{{ $inputClass }}" required>
                                <option value="">— Pilih —</option>
                                @foreach (\App\Enums\PublishedFor::cases() as $publishedFor)
                                    <option value="{{ $publishedFor->value }}" @selected(old('published_for', $publication?->published_for?->value) === $publishedFor->value)>{{ $publishedFor->label() }}</option>
                                @endforeach
                            </select>
                        </x-field>
                        <x-field name="cover_maker" label="Pembuat Cover" required>
                            <select name="cover_maker" class="{{ $inputClass }}" required>
                                <option value="">— Pilih —</option>
                                @foreach (\App\Enums\CoverMaker::cases() as $coverMaker)
                                    <option value="{{ $coverMaker->value }}" @selected(old('cover_maker', $publication?->cover_maker?->value) === $coverMaker->value)>{{ $coverMaker->label() }}</option>
                                @endforeach
                            </select>
                        </x-field>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-field name="orientation" label="Orientasi" required>
                                <select name="orientation" class="{{ $inputClass }}" required>
                                    <option value="">— Pilih —</option>
                                    @foreach (\App\Enums\Orientation::cases() as $orientation)
                                        <option value="{{ $orientation->value }}" @selected(old('orientation', $publication?->orientation?->value) === $orientation->value)>{{ $orientation->label() }}</option>
                                    @endforeach
                                </select>
                            </x-field>
                            <x-field name="paper_size" label="Ukuran Kertas" required>
                                <select name="paper_size" class="{{ $inputClass }}" required>
                                    <option value="">— Pilih —</option>
                                    @foreach (\App\Http\Requests\StorePublicationRequest::PAPER_SIZES as $sizeKey => $sizeLabel)
                                        <option value="{{ $sizeKey }}" @selected(old('paper_size', $publication?->paper_size) === $sizeKey)>{{ $sizeLabel }}</option>
                                    @endforeach
                                </select>
                            </x-field>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="mb-4 font-semibold">File Naskah</h2>
                <div class="grid gap-4">
                    @if ($isEdit && $publication->latestFile)
                        <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                            File saat ini: <a href="{{ route('files.publication', $publication->latestFile) }}" target="_blank" class="font-semibold text-blue-700 underline">{{ $publication->latestFile->original_name }}</a> (versi {{ $publication->latestFile->version }}).
                            Kosongkan jika tidak mengganti file.
                        </p>
                    @endif
                    <x-field name="file" label="Softcopy Naskah Publikasi (PDF)" :required="! $isEdit" hint="Maksimal 30 MB.">
                        <input type="file" name="file" id="file" accept="application/pdf" class="{{ $inputClass }}" @required(! $isEdit)>
                    </x-field>
                    <x-field name="drive_link" label="Link Drive File Mentah Naskah" required hint="Link Google Drive berisi file mentah (docx/excel/referensi) sebelum menjadi PDF.">
                        <input type="url" name="drive_link" id="drive_link" class="{{ $inputClass }}" placeholder="https://drive.google.com/..." value="{{ old('drive_link', $publication?->latestFile?->drive_link) }}" required>
                    </x-field>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ $isEdit ? route('publications.show', $publication) : route('publications.create') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="rounded-lg bg-blue-800 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ $isEdit ? 'Ajukan Ulang Hasil Revisi' : 'Submit Pengajuan Publikasi' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        const arcSelect = document.getElementById('arc_publication_id');
        const preview = document.getElementById('title_en_preview');
        const syncPreview = () => {
            if (!arcSelect || !preview) return;
            preview.value = arcSelect.selectedOptions[0]?.dataset.titleEn ?? '';
        };
        arcSelect?.addEventListener('change', syncPreview);
        syncPreview();

        const romawiInput = document.getElementById('pages_romawi');
        const romawiListbox = document.getElementById('romawi-listbox');
        if (romawiInput && romawiListbox) {
            const options = Array.from(romawiListbox.querySelectorAll('.romawi-option'));

            const filterRomawi = () => {
                const query = romawiInput.value.trim().toUpperCase();
                if (query.length === 0) {
                    romawiListbox.classList.add('hidden');
                    romawiInput.setAttribute('aria-expanded', 'false');
                    return;
                }

                let visibleCount = 0;
                options.forEach(opt => {
                    const roman = opt.dataset.roman || '';
                    const num = opt.dataset.number || '';
                    const match = roman.includes(query) || num.startsWith(query);
                    opt.classList.toggle('hidden', !match);
                    if (match) visibleCount++;
                });

                const show = visibleCount > 0;
                romawiListbox.classList.toggle('hidden', !show);
                romawiInput.setAttribute('aria-expanded', show ? 'true' : 'false');
            };

            romawiInput.addEventListener('input', filterRomawi);

            options.forEach(opt => {
                opt.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    romawiInput.value = opt.dataset.roman || '';
                    romawiListbox.classList.add('hidden');
                    romawiInput.setAttribute('aria-expanded', 'false');
                });
            });

            document.addEventListener('click', (e) => {
                if (!document.getElementById('romawi-combobox')?.contains(e.target)) {
                    romawiListbox.classList.add('hidden');
                    romawiInput.setAttribute('aria-expanded', 'false');
                }
            });
        }
    </script>
@endsection
@extends('layouts.app')

@section('title', 'Detail Publikasi')

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">{{ $publication->title }}</h2>
                <x-status-badge :status="$publication->status" />
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $publication->number }} &middot; {{ $publication->type->label() }} &middot; diajukan {{ $publication->submitted_at?->format('d/m/Y H:i') }} WIB</p>
        </div>
        <a href="{{ route('publications.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">&larr; Daftar Publikasi</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="mb-4 font-semibold">Metadata Publikasi</h3>
                <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">Judul</dt><dd class="font-medium">{{ $publication->title }}</dd></div>
                    <div><dt class="text-slate-500">Judul Inggris</dt><dd class="font-medium">{{ $publication->title_en ?? '-' }}</dd></div>
                    <div><dt class="text-slate-500">Pembuat Naskah</dt><dd class="font-medium">{{ $publication->user->name }}</dd></div>
                    <div><dt class="text-slate-500">Tim</dt><dd class="font-medium">{{ $publication->team->name }}@if ($publication->team->code) <span class="font-mono text-xs text-slate-500">({{ $publication->team->code }})</span>@endif</dd></div>
                    <div><dt class="text-slate-500">Frekuensi Terbit</dt><dd class="font-medium">{{ $publication->frequency->label() }}</dd></div>
                    <div><dt class="text-slate-500">Bahasa</dt><dd class="font-medium">{{ $publication->language->label() }}</dd></div>
                    <div><dt class="text-slate-500">{{ $publication->type === \App\Enums\PublicationType::Arc ? 'Jadwal Rilis' : 'Estimasi Rilis' }}</dt><dd class="font-medium">{{ $publication->release_date->format('m/d/Y') }}</dd></div>
                    @if ($publication->type === \App\Enums\PublicationType::NonArc)
                        <div><dt class="text-slate-500">Tahun Terbit</dt><dd class="font-medium">{{ $publication->year }}</dd></div>
                        <div><dt class="text-slate-500">Jumlah Halaman Romawi</dt><dd class="font-medium">{{ \App\Support\RomanNumeral::fromInt($publication->pages_romawi) ?: $publication->pages_romawi }} ({{ $publication->pages_romawi }})</dd></div>
                        <div><dt class="text-slate-500">Jumlah Halaman Arab</dt><dd class="font-medium">{{ $publication->pages_arabic }}</dd></div>
                        <div><dt class="text-slate-500">Kerjasama Instansi Luar</dt><dd class="font-medium">{{ $publication->has_cooperation ? 'Ya' : 'Tidak' }}</dd></div>
                        <div><dt class="text-slate-500">Diterbitkan untuk</dt><dd class="font-medium">{{ $publication->published_for?->label() }}</dd></div>
                        <div><dt class="text-slate-500">Pembuat Cover</dt><dd class="font-medium">{{ $publication->cover_maker?->label() }}</dd></div>
                        <div><dt class="text-slate-500">Orientasi</dt><dd class="font-medium">{{ $publication->orientation?->label() }}</dd></div>
                        <div><dt class="text-slate-500">Ukuran Kertas</dt><dd class="font-medium">{{ \App\Http\Requests\StorePublicationRequest::PAPER_SIZES[$publication->paper_size] ?? $publication->paper_size }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="mb-4 font-semibold">File Naskah</h3>
                @if ($publication->latestFile)
                    <div class="mb-4 rounded-lg bg-blue-50 px-4 py-3 text-sm">
                        <p class="font-semibold text-blue-900">Versi terbaru (v{{ $publication->latestFile->version }})</p>
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                            <a href="{{ route('files.publication', $publication->latestFile) }}" target="_blank" class="font-semibold text-blue-700 underline">Lihat PDF: {{ $publication->latestFile->original_name }}</a>
                            <a href="{{ $publication->latestFile->drive_link }}" target="_blank" rel="noopener" class="font-semibold text-blue-700 underline">Link Drive file mentah</a>
                        </div>
                    </div>
                @endif
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase text-slate-500">
                        <tr><th class="py-2 pr-4">Versi</th><th class="py-2 pr-4">File</th><th class="py-2 pr-4">Diunggah oleh</th><th class="py-2 pr-4">Tanggal</th><th class="py-2">Drive</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($publication->files->reverse() as $file)
                            <tr>
                                <td class="py-2 pr-4">v{{ $file->version }}</td>
                                <td class="py-2 pr-4"><a href="{{ route('files.publication', $file) }}" target="_blank" class="text-blue-700 hover:underline">{{ $file->original_name }}</a></td>
                                <td class="py-2 pr-4">{{ $file->uploader?->name ?? '-' }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2"><a href="{{ $file->drive_link }}" target="_blank" rel="noopener" class="text-blue-700 hover:underline">Buka</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($publication->qualityCards->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-6">
                    <h3 class="mb-4 font-semibold">Kartu Kendali Kualitas</h3>
                    <div class="space-y-3">
                        @foreach ($publication->qualityCards->reverse() as $card)
                            <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 px-4 py-3 text-sm">
                                <div>
                                    <p class="font-medium">Diisi oleh {{ $card->ketuaTim->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $card->approved_at->format('d/m/Y H:i') }} WIB</p>
                                </div>
                                @if ($card->pdf_path)
                                    <a href="{{ route('files.quality-card', $card) }}" target="_blank" class="font-semibold text-blue-700 hover:underline">Lihat PDF</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($publication->revisions->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-white p-6">
                    <h3 class="mb-4 font-semibold">Riwayat Revisi</h3>
                    <div class="space-y-3">
                        @foreach ($publication->revisions->reverse() as $revision)
                            <div class="rounded-lg border {{ $revision->resolved_at ? 'border-slate-200' : 'border-red-200 bg-red-50' }} px-4 py-3 text-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-semibold">{{ $revision->stage->label() }} &middot; {{ $revision->requester->name }}</p>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="text-slate-500">{{ $revision->created_at->format('d/m/Y H:i') }}</span>
                                        @if ($revision->resolved_at)
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 font-semibold text-green-700">Sudah diperbaiki</span>
                                        @else
                                            <span class="rounded-full bg-red-100 px-2 py-0.5 font-semibold text-red-700">Menunggu perbaikan</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="mt-1 whitespace-pre-line text-slate-700">{{ $revision->comment }}</p>
                                @if ($revision->file_path)
                                    <a href="{{ route('files.revision', $revision) }}" target="_blank" class="mt-1 inline-block font-semibold text-blue-700 underline">File detail revisi (PDF)</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            @can('update', $publication)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h3 class="font-semibold text-amber-900">Naskah perlu diperbaiki</h3>
                    <p class="mt-1 text-sm text-amber-800">Perbaiki metadata/file sesuai catatan revisi, lalu ajukan ulang.</p>
                    <a href="{{ route('publications.edit', $publication) }}" class="mt-3 block rounded-lg bg-amber-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-amber-500">Perbaiki Naskah</a>
                </div>
            @endcan

            @can('reviewContent', $publication)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <h3 class="font-semibold text-blue-900">Pemeriksaan Konten</h3>
                    <p class="mt-1 text-sm text-blue-800">Periksa isi naskah, lalu isi kartu kendali kualitas untuk menyetujui.</p>
                    <a href="{{ route('quality-cards.create', $publication) }}" class="mt-3 block rounded-lg bg-blue-800 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-blue-700">Isi Kartu Kendali & Approve</a>
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm font-semibold text-red-700">Minta Revisi</summary>
                        <form method="POST" action="{{ route('revisions.store', $publication) }}" class="mt-2 space-y-2">
                            @csrf
                            <textarea name="comment" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Catatan perbaikan konten..." required></textarea>
                            <button type="submit" class="w-full rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Kirim Catatan Revisi</button>
                        </form>
                    </details>
                </div>
            @endcan

            @can('reviewDls', $publication)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <h3 class="font-semibold text-blue-900">Review Layout & Non-Konten</h3>
                    <form method="POST" action="{{ route('reviews.dls.forward', $publication) }}" class="mt-3" onsubmit="return confirm('Teruskan naskah ini ke Ketua DLS untuk pengecekan akhir?')">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Teruskan ke Ketua DLS</button>
                    </form>
                    <details class="mt-3" open>
                        <summary class="cursor-pointer text-sm font-semibold text-red-700">Ajukan Revisi</summary>
                        <form method="POST" action="{{ route('revisions.store', $publication) }}" enctype="multipart/form-data" class="mt-2 space-y-2">
                            @csrf
                            <textarea name="comment" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Komentar revisi layout/non-konten..." required></textarea>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">File detail revisi (PDF, wajib)</label>
                                <input type="file" name="file" accept="application/pdf" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            </div>
                            <button type="submit" class="w-full rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Kembalikan untuk Revisi</button>
                        </form>
                    </details>
                </div>
            @endcan

            @can('reviewFinal', $publication)
                <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <h3 class="font-semibold text-blue-900">Pengecekan Akhir</h3>
                    <form method="POST" action="{{ route('reviews.final.approve', $publication) }}" class="mt-3" onsubmit="return confirm('Setujui publikasi ini? Status akan menjadi Selesai.')">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-600">Setujui Publikasi</button>
                    </form>
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm font-semibold text-red-700">Minta Revisi</summary>
                        <form method="POST" action="{{ route('revisions.store', $publication) }}" class="mt-2 space-y-2">
                            @csrf
                            <textarea name="comment" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Catatan perbaikan akhir..." required></textarea>
                            <button type="submit" class="w-full rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Kirim Catatan Revisi</button>
                        </form>
                    </details>
                </div>
            @endcan

            @can('downloadDocuments', $publication)
                <div class="rounded-xl border border-green-200 bg-green-50 p-5">
                    <h3 class="font-semibold text-green-900">Dokumen Final</h3>
                    <p class="mt-1 text-sm text-green-800">Naskah selesai. Unduh dokumen untuk diunggah ke portal publikasi pusat.</p>
                    <a href="{{ route('documents.download', $publication) }}" class="mt-3 block rounded-lg bg-green-700 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-green-600">Unduh Semua Dokumen (ZIP)</a>
                    <div class="mt-2 space-y-1 text-sm">
                        @if ($publication->latestFile)
                            <a href="{{ route('files.publication', $publication->latestFile) }}" target="_blank" class="block font-semibold text-green-800 underline">Softcopy naskah final (PDF)</a>
                        @endif
                        @if ($publication->latestQualityCard?->pdf_path)
                            <a href="{{ route('files.quality-card', $publication->latestQualityCard) }}" target="_blank" class="block font-semibold text-green-800 underline">Kartu kendali (PDF)</a>
                        @endif
                    </div>
                </div>
            @endcan

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <h3 class="mb-4 font-semibold">Timeline Proses</h3>
                <x-timeline :events="$publication->timelineEvents" :revisions="$publication->revisions->keyBy('id')" />
            </div>
        </div>
    </div>
@endsection
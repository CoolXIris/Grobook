@extends('layouts.app')

@section('title', 'Kartu Kendali Kualitas')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold">{{ $publication->title }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $publication->number }} &middot; {{ $publication->type->label() }} &middot; {{ $publication->team->name }} &middot; oleh {{ $publication->user->name }}</p>
                    <p class="text-sm text-slate-500">Rilis: {{ $publication->release_date->format('m/d/Y') }} &middot; {{ $publication->frequency->label() }} &middot; {{ $publication->language->label() }}</p>
                </div>
                @if ($publication->latestFile)
                    <a href="{{ route('files.publication', $publication->latestFile) }}" target="_blank" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Lihat Naskah (PDF)</a>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('quality-cards.store', $publication) }}" class="space-y-6">
            @csrf

            <div class="rounded-xl border border-slate-200 bg-white p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="font-semibold">Manual Pemeriksaan Mandiri Publikasi</h3>
                        <p class="text-xs text-slate-500">Isi setiap butir: Sesuai / Tidak Sesuai / Tidak Relevan. Semua butir wajib diisi. Publikasi hanya dapat disetujui jika tidak ada butir yang Tidak Sesuai.</p>
                    </div>
                    <button type="button" id="mark-all" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Tandai semua Sesuai</button>
                </div>

                @foreach ($sections as $section)
                    <div class="mb-6">
                        <h4 class="rounded-lg bg-[#043277] px-3.5 py-2 text-sm font-semibold tracking-wide text-white shadow-2xs">{{ $section->name }}</h4>
                        @php($lastGroup = null)
                        @foreach ($section->activeItems as $item)
                            @if ($item->group_label && $item->group_label !== $lastGroup)
                                <p class="mt-2 bg-slate-100 px-3 py-1.5 text-xs font-bold italic text-slate-600">{{ $item->group_label }}</p>
                                @php($lastGroup = $item->group_label)
                            @endif
                            <div class="border-b border-slate-100 px-3 py-3">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <p class="min-w-0 flex-1 whitespace-pre-line text-sm text-slate-700"><span class="font-semibold">{{ $item->number }}.</span> {{ $item->content }}</p>
                                    <div class="flex shrink-0 gap-3 text-xs">
                                        @foreach (\App\Enums\ChecklistStatus::cases() as $status)
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="answers[{{ $item->id }}][status]" value="{{ $status->value }}" class="status-radio" @checked(old("answers.{$item->id}.status") === $status->value) required>
                                                {{ $status->label() }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <input type="text" name="answers[{{ $item->id }}][note]" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs" placeholder="Catatan (opsional)" value="{{ old("answers.{$item->id}.note") }}">
                                @error("answers.{$item->id}.status")
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('publications.show', $publication) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="rounded-lg bg-green-700 px-5 py-2 text-sm font-semibold text-white hover:bg-green-600" onclick="return confirm('Setujui publikasi ini? Sistem akan membuat PDF kartu kendali dan meneruskan naskah ke Petugas DLS.')">Approve Publikasi & Generate Kartu Kendali</button>
            </div>
        </form>

        <details class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5">
            <summary class="cursor-pointer text-sm font-semibold text-red-700">Ada konten yang perlu diperbaiki? Minta Revisi ke tim pembuat</summary>
            <form method="POST" action="{{ route('revisions.store', $publication) }}" class="mt-3 space-y-2">
                @csrf
                <textarea name="comment" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Catatan perbaikan konten..." required></textarea>
                <button type="submit" class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">Kirim Catatan Revisi</button>
            </form>
        </details>
    </div>

    <script>
        document.getElementById('mark-all')?.addEventListener('click', () => {
            document.querySelectorAll('.status-radio[value="sesuai"]').forEach(radio => radio.checked = true);
        });
    </script>
@endsection
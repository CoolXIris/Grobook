@extends('layouts.app')

@section('title', 'Unduh Dokumen Final')

@section('content')
    <p class="mb-4 text-sm text-slate-600">Publikasi yang telah disetujui Ketua DLS (status Selesai). Unduh paket berisi naskah final (PDF) dan kartu kendali kualitas (PDF).</p>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Pembuat / Tim</th>
                    <th class="px-4 py-3">Selesai Pada</th>
                    <th class="px-4 py-3 w-56"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($publications as $publication)
                    <tr class="hover:bg-slate-50">
                        <td class="max-w-md px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $publication->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $publication->number }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $publication->type === \App\Enums\PublicationType::Arc ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700' }}">{{ $publication->type->label() }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p>{{ $publication->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $publication->team->name }}</p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $publication->completed_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('publications.show', $publication) }}" class="font-semibold text-slate-600 hover:underline">Detail</a>
                            <a href="{{ route('documents.download', $publication) }}" class="ml-3 rounded-lg bg-blue-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">Unduh ZIP</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada publikasi berstatus Selesai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($publications->hasPages())
        <div class="mt-4">{{ $publications->links() }}</div>
    @endif
@endsection
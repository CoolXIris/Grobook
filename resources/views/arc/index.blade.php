@extends('layouts.app')

@section('title', 'Daftar Publikasi ARC')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">Master judul publikasi ARC. Judul pada form pengajuan ARC diambil dari daftar ini. Judul yang nonaktif tidak bisa dipilih pada pengajuan baru.</p>
        <a href="{{ route('arc-publications.create') }}" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah Judul</a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Judul (Indonesia)</th>
                    <th class="px-4 py-3">Judul (Inggris)</th>
                    <th class="px-4 py-3 w-24">Status</th>
                    <th class="px-4 py-3 w-36"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($arcPublications as $arc)
                    <tr class="hover:bg-slate-50 {{ $arc->is_active ? '' : 'opacity-60' }}">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $arc->title }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $arc->title_en }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $arc->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-600' }}">{{ $arc->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('arc-publications.edit', $arc) }}" class="font-semibold text-blue-700 hover:underline">Edit</a>
                            <form action="{{ route('arc-publications.destroy', $arc) }}" method="POST" class="ml-2 inline" onsubmit="return confirm('Hapus judul ARC ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada judul publikasi ARC. Tambahkan judul sesuai daftar resmi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($arcPublications->hasPages())
        <div class="mt-4">{{ $arcPublications->links() }}</div>
    @endif
@endsection
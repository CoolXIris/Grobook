@extends('layouts.app')

@section('title', 'Kartu Kendali — Butir Pemeriksaan')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">Kelola bagian (judul kategori) dan butir pemeriksaan kartu kendali kualitas. Bagian maupun butir dapat ditambah, diubah, atau dihapus kapan saja.</p>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('checklist-sections.create') }}" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100">+ Tambah Bagian</a>
            <a href="{{ route('checklist-items.create') }}" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah Butir</a>
        </div>
    </div>

    @foreach ($sections as $section)
        <div class="mb-6 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700">{{ $section->position }}. {{ $section->name }}</h2>
                <div class="flex items-center gap-3 text-xs">
                    <a href="{{ route('checklist-items.create', ['section' => $section->id]) }}" class="font-semibold text-slate-600 hover:text-blue-700 hover:underline">+ Butir</a>
                    <a href="{{ route('checklist-sections.edit', $section) }}" class="font-semibold text-blue-700 hover:underline">Edit Bagian</a>
                    <form action="{{ route('checklist-sections.destroy', $section) }}" method="POST" class="inline" onsubmit="return confirm('Hapus bagian &quot;{{ $section->position }}. {{ $section->name }}&quot; beserta seluruh butir di dalamnya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="font-semibold text-red-600 hover:underline">Hapus Bagian</button>
                    </form>
                </div>
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-2 w-16">No.</th>
                        <th class="px-4 py-2">Butir Pemeriksaan</th>
                        <th class="px-4 py-2 w-24">Status</th>
                        <th class="px-4 py-2 w-36"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($section->items as $item)
                        <tr class="hover:bg-slate-50 {{ $item->is_active ? '' : 'opacity-60' }}">
                            <td class="px-4 py-2 align-top">{{ $item->number }}</td>
                            <td class="px-4 py-2">
                                @if ($item->group_label)
                                    <p class="text-xs font-semibold uppercase text-slate-500">{{ $item->group_label }}</p>
                                @endif
                                <p class="text-slate-800">{{ $item->content }}</p>
                            </td>
                            <td class="px-4 py-2 align-top">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-600' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="px-4 py-2 align-top text-right whitespace-nowrap">
                                <a href="{{ route('checklist-items.edit', $item) }}" class="font-semibold text-blue-700 hover:underline">Edit</a>
                                <form action="{{ route('checklist-items.destroy', $item) }}" method="POST" class="ml-2 inline" onsubmit="return confirm('Hapus butir pemeriksaan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-4 text-center text-slate-500">Belum ada butir pada bagian ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach
@endsection
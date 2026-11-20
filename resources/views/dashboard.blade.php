@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($counts as $label => $value)
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-2xs">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 tabular-nums">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="flex flex-col rounded-xl border border-slate-200/80 bg-white shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800">{{ $actionLabel }}</h2>
                <a href="{{ route($actionRoute) }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900">Lihat semua &rarr;</a>
            </div>
            <div class="flex-1 space-y-2.5 p-5">
                @forelse ($actionItems as $publication)
                    <a href="{{ route('publications.show', $publication) }}" class="block rounded-lg border border-slate-200/80 px-4 py-3 transition hover:border-blue-300 hover:bg-slate-50/70">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium text-slate-900">{{ $publication->title }}</p>
                            <x-status-badge :status="$publication->status" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500"><span class="font-mono text-slate-400">{{ $publication->number }}</span> &middot; {{ $publication->user->name }}</p>
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm text-slate-400">Tidak ada naskah yang menunggu tindakan Anda.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="flex flex-col rounded-xl border border-slate-200/80 bg-white shadow-2xs">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800">Publikasi Terbaru</h2>
                <a href="{{ route('publications.index') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900">Lihat semua &rarr;</a>
            </div>
            <div class="flex-1 space-y-2.5 p-5">
                @forelse ($recent as $publication)
                    <a href="{{ route('publications.show', $publication) }}" class="block rounded-lg border border-slate-200/80 px-4 py-3 transition hover:border-blue-300 hover:bg-slate-50/70">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium text-slate-900">{{ $publication->title }}</p>
                            <x-status-badge :status="$publication->status" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500"><span class="font-mono text-slate-400">{{ $publication->number }}</span> &middot; {{ $publication->team->name }} &middot; {{ $publication->created_at->format('d/m/Y') }}</p>
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-sm text-slate-400">Belum ada publikasi.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
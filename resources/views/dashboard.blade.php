@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ($counts as $label => $value)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-3xl font-bold text-blue-950">{{ $value }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold">{{ $actionLabel }}</h2>
                <a href="{{ route($actionRoute) }}" class="text-sm font-semibold text-blue-700 hover:underline">Lihat semua</a>
            </div>
            <div class="space-y-3">
                @forelse ($actionItems as $publication)
                    <a href="{{ route('publications.show', $publication) }}" class="block rounded-lg border border-slate-200 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium">{{ $publication->title }}</p>
                            <x-status-badge :status="$publication->status" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $publication->number }} &middot; {{ $publication->user->name }}</p>
                    </a>
                @empty
                    <p class="py-4 text-center text-sm text-slate-500">Tidak ada naskah yang menunggu tindakan Anda.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-semibold">Publikasi Terbaru</h2>
                <a href="{{ route('publications.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">Lihat semua</a>
            </div>
            <div class="space-y-3">
                @forelse ($recent as $publication)
                    <a href="{{ route('publications.show', $publication) }}" class="block rounded-lg border border-slate-200 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium">{{ $publication->title }}</p>
                            <x-status-badge :status="$publication->status" />
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $publication->number }} &middot; {{ $publication->team->name }} &middot; {{ $publication->created_at->format('d/m/Y') }}</p>
                    </a>
                @empty
                    <p class="py-4 text-center text-sm text-slate-500">Belum ada publikasi.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
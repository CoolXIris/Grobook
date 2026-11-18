@extends('layouts.app')

@section('title', 'Daftar Publikasi')

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('publications.index') }}" class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusFilter === null ? 'bg-blue-800 text-white' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50' }}">Semua</a>
        @foreach ($statuses as $status)
            <a href="{{ route('publications.index', ['status' => $status->value]) }}" class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $statusFilter === $status ? 'bg-blue-800 text-white' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50' }}">{{ $status->label() }}</a>
        @endforeach
    </div>

    <x-publication-table :publications="$publications" empty-text="Belum ada publikasi pada filter ini." />
@endsection
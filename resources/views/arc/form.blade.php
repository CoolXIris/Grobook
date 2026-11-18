@extends('layouts.app')

@section('title', $arcPublication->exists ? 'Edit Judul ARC' : 'Tambah Judul ARC')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <form action="{{ $arcPublication->exists ? route('arc-publications.update', $arcPublication) : route('arc-publications.store') }}" method="POST" class="space-y-4">
            @csrf
            @if ($arcPublication->exists)
                @method('PUT')
            @endif

            <x-field name="title" label="Judul Publikasi (Indonesia)" required>
                <input type="text" id="title" name="title" value="{{ old('title', $arcPublication->title) }}" maxlength="500" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
            </x-field>

            <x-field name="title_en" label="Judul Publikasi (Inggris)" required>
                <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $arcPublication->title_en) }}" maxlength="500" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
            </x-field>

            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $arcPublication->is_active ?? true))>
                Aktif (bisa dipilih pada pengajuan ARC baru)
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $arcPublication->exists ? 'Simpan Perubahan' : 'Tambah Judul' }}</button>
                <a href="{{ route('arc-publications.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
@endsection
@extends('layouts.app')

@section('title', $section->exists ? 'Edit Bagian Kartu Kendali' : 'Tambah Bagian Kartu Kendali')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <form action="{{ $section->exists ? route('checklist-sections.update', $section) : route('checklist-sections.store') }}" method="POST" class="space-y-4">
            @csrf
            @if ($section->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <x-field name="position" label="Nomor Urut Bagian" required hint="Mis. 8">
                    <input type="number" id="position" name="position" value="{{ old('position', $section->position) }}" min="1" max="999" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </x-field>
                <div class="sm:col-span-2">
                    <x-field name="name" label="Judul Bagian Kartu Kendali" required hint="Mis. PEMANFAATAN ADOBE">
                        <input type="text" id="name" name="name" value="{{ old('name', $section->name) }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </x-field>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $section->exists ? 'Simpan Perubahan' : 'Tambah Bagian' }}</button>
                <a href="{{ route('checklist-items.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
@endsection

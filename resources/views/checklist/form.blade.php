@extends('layouts.app')

@section('title', $item->exists ? 'Edit Butir Pemeriksaan' : 'Tambah Butir Pemeriksaan')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <form action="{{ $item->exists ? route('checklist-items.update', $item) : route('checklist-items.store') }}" method="POST" class="space-y-4">
            @csrf
            @if ($item->exists)
                @method('PUT')
            @endif

            <x-field name="checklist_section_id" label="Bagian Kartu Kendali" required>
                <select id="checklist_section_id" name="checklist_section_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected(old('checklist_section_id', $item->checklist_section_id) == $section->id)>{{ $section->position }}. {{ $section->name }}</option>
                    @endforeach
                </select>
            </x-field>

            <div class="grid grid-cols-2 gap-4">
                <x-field name="number" label="Nomor Butir" required hint="Nomor urut yang tampil pada kartu kendali.">
                    <input type="number" id="number" name="number" value="{{ old('number', $item->number) }}" min="1" max="999" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </x-field>
                <x-field name="position" label="Posisi Urut" required hint="Urutan tampil dalam bagian.">
                    <input type="number" id="position" name="position" value="{{ old('position', $item->position ?? $item->number) }}" min="1" max="999" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </x-field>
            </div>

            <x-field name="group_label" label="Label Kelompok" hint="Opsional, mis. KOVER DEPAN, HALAMAN KATALOG. Ditampilkan sebagai sub-judul di atas butir.">
                <input type="text" id="group_label" name="group_label" value="{{ old('group_label', $item->group_label) }}" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </x-field>

            <x-field name="content" label="Isi Butir Pemeriksaan" required>
                <textarea id="content" name="content" rows="3" maxlength="5000" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>{{ old('content', $item->content) }}</textarea>
            </x-field>

            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $item->is_active ?? true))>
                Aktif (muncul pada kartu kendali baru)
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $item->exists ? 'Simpan Perubahan' : 'Tambah Butir' }}</button>
                <a href="{{ route('checklist-items.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
@endsection
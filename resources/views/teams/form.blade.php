@extends('layouts.app')

@section('title', $team->exists ? 'Edit Tim' : 'Daftarkan Tim')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <form action="{{ $team->exists ? route('teams.update', $team) : route('teams.store') }}" method="POST" class="space-y-4">
            @csrf
            @if ($team->exists)
                @method('PUT')
            @endif

            <x-field name="name" label="Nama Tim" required hint="Mis. Tim Penyusun Naskah Sosial.">
                <input type="text" id="name" name="name" value="{{ old('name', $team->name) }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
            </x-field>

            @if ($team->exists)
                <x-field name="ketua_tim_email" label="Akun Google Ketua Tim">
                    <input type="text" value="{{ $ketuaTimEmail }}" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500" disabled>
                    <p class="mt-1 text-xs text-slate-500">Kepemimpinan tim dipindahkan melalui menu Kelola User (Ketua DLS).</p>
                </x-field>
            @else
                <x-field name="ketua_tim_email" label="Akun Google Ketua Tim (email)" required hint="Jika akun belum ada, sistem akan mendaftarkannya dengan role Ketua Tim. Akun aktif saat yang bersangkutan login dengan Google.">
                    <input type="email" id="ketua_tim_email" name="ketua_tim_email" value="{{ old('ketua_tim_email') }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </x-field>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $team->exists ? 'Simpan Perubahan' : 'Daftarkan Tim' }}</button>
                <a href="{{ route('teams.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
@endsection
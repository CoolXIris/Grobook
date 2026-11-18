@extends('layouts.app')

@section('title', $user->exists ? 'Edit Akun' : 'Daftarkan Akun')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            @if ($user->exists)
                @method('PUT')
            @endif

            <x-field name="name" label="Nama" :required="$user->exists" hint="{{ $user->exists ? '' : 'Opsional. Jika kosong, diisi dari bagian depan email.' }}">
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" @required($user->exists)>
            </x-field>

            @if (! $user->exists)
                <x-field name="email" label="Akun Google (email)" required hint="Akun akan aktif saat yang bersangkutan login dengan Google.">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                </x-field>
            @else
                <x-field name="email" label="Akun Google (email)">
                    <input type="text" value="{{ $user->email }}" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500" disabled>
                </x-field>
            @endif

            <x-field name="role" label="Role" required>
                <select id="role" name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    @foreach (\App\Enums\Role::cases() as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </x-field>

            @if ($user->exists)
                <x-field name="team_id" label="Tim" hint="Hanya berlaku untuk role Tim Pembuat Naskah.">
                    <select id="team_id" name="team_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">— Tanpa tim —</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $user->team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </x-field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="nip" label="NIP">
                        <input type="text" id="nip" name="nip" value="{{ old('nip', $user->nip) }}" maxlength="30" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </x-field>
                    <x-field name="phone" label="No. Telepon">
                        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </x-field>
                </div>

                <x-field name="jabatan" label="Jabatan">
                    <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </x-field>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $user->exists ? 'Simpan Perubahan' : 'Daftarkan Akun' }}</button>
                <a href="{{ route('users.index') }}" class="text-sm font-semibold text-slate-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
@endsection
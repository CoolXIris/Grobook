@extends('layouts.app')

@section('title', 'Anggota Tim')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <h2 class="text-sm font-bold text-slate-700">{{ $team->name }}</h2>
                    <p class="text-xs text-slate-500">Ketua tim: {{ $team->ketuaTim?->name }} ({{ $team->ketuaTim?->email }})</p>
                </div>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-2">Nama</th>
                            <th class="px-4 py-2">Akun Google</th>
                            <th class="px-4 py-2 w-24"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($team->members as $member)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2 font-medium text-slate-800">{{ $member->name }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $member->email }}</td>
                                <td class="px-4 py-2 text-right">
                                    <form action="{{ route('team-members.destroy', $member) }}" method="POST" class="inline" onsubmit="return confirm('Keluarkan {{ $member->name }} dari tim?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-red-600 hover:underline">Keluarkan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Belum ada anggota. Daftarkan akun Google tim pembuat naskah melalui form di samping.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="mb-1 text-sm font-bold text-slate-700">Daftarkan Anggota</h2>
                <p class="mb-4 text-xs text-slate-500">Masukkan akun Google (email) tim pembuat naskah. Jika belum terdaftar, akun dibuat dengan role Tim Pembuat Naskah dan aktif saat yang bersangkutan login dengan Google.</p>
                <form action="{{ route('team-members.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <x-field name="email" label="Akun Google (email)" required>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </x-field>
                    <button type="submit" class="w-full rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Daftarkan ke Tim</button>
                </form>
            </div>
        </div>
    </div>
@endsection
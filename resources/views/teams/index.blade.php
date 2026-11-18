@extends('layouts.app')

@section('title', 'Ketua Tim & Tim Naskah')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">Daftarkan tim beserta akun Google ketua timnya. Ketua tim kemudian dapat mendaftarkan anggota tim pembuat naskah melalui menu Anggota Tim.</p>
        <a href="{{ route('teams.create') }}" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Daftarkan Tim</a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama Tim</th>
                    <th class="px-4 py-3">Ketua Tim</th>
                    <th class="px-4 py-3 w-28">Jumlah Anggota</th>
                    <th class="px-4 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($teams as $team)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $team->name }}</td>
                        <td class="px-4 py-3">
                            <p>{{ $team->ketuaTim?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $team->ketuaTim?->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $team->members_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('teams.edit', $team) }}" class="font-semibold text-blue-700 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada tim terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($teams->hasPages())
        <div class="mt-4">{{ $teams->links() }}</div>
    @endif
@endsection
@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('users.index') }}" class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $roleFilter === null ? 'bg-blue-800 text-white' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50' }}">Semua</a>
            @foreach ($roles as $role)
                <a href="{{ route('users.index', ['role' => $role->value]) }}" class="rounded-full px-3 py-1.5 text-sm font-semibold {{ $roleFilter === $role ? 'bg-blue-800 text-white' : 'bg-white text-slate-600 border border-slate-300 hover:bg-slate-50' }}">{{ $role->label() }}</a>
            @endforeach
        </div>
        <a href="{{ route('users.create') }}" class="rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Daftarkan Akun</a>
    </div>

    @error('user')
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Akun Google</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Tim</th>
                    <th class="px-4 py-3 w-36"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">Anda</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($user->assignedRoles() as $assignedRole)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $user->role === $assignedRole ? 'bg-blue-50 text-blue-800 ring-1 ring-inset ring-blue-600/20' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $assignedRole->label() }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->team?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('users.edit', $user) }}" class="font-semibold text-blue-700 hover:underline">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="ml-2 inline" onsubmit="return confirm('Hapus akun {{ $user->email }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada akun pada filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
@endsection
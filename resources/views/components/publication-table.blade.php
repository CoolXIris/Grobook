@props(['publications', 'emptyText' => 'Belum ada publikasi.'])

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Judul</th>
                <th class="px-4 py-3">Tipe</th>
                <th class="px-4 py-3">Pembuat / Tim</th>
                <th class="px-4 py-3">Rilis</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($publications as $publication)
                <tr class="hover:bg-slate-50">
                    <td class="max-w-md px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $publication->title }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $publication->number }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $publication->type === \App\Enums\PublicationType::Arc ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700' }}">{{ $publication->type->label() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <p>{{ $publication->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $publication->team->name }}</p>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $publication->release_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3"><x-status-badge :status="$publication->status" /></td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('publications.show', $publication) }}" class="font-semibold text-blue-700 hover:underline">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ $emptyText }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($publications instanceof \Illuminate\Pagination\AbstractPaginator && $publications->hasPages())
    <div class="mt-4">{{ $publications->links() }}</div>
@endif
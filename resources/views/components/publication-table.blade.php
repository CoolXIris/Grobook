@props(['publications', 'emptyText' => 'Belum ada publikasi.'])

<div class="overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-2xs">
    <table class="min-w-full divide-y divide-slate-200/80 text-sm">
        <thead class="bg-slate-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
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
                <tr class="transition-colors hover:bg-slate-50/80">
                    <td class="max-w-md px-4 py-3.5">
                        <p class="font-medium text-slate-900">{{ $publication->title }}</p>
                        <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $publication->number }}</p>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $publication->type === \App\Enums\PublicationType::Arc ? 'bg-indigo-50 text-indigo-700 ring-indigo-600/20' : 'bg-slate-100 text-slate-700 ring-slate-500/20' }}">{{ $publication->type->label() }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="font-medium text-slate-700">{{ $publication->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $publication->team->name }}</p>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-slate-600">{{ $publication->release_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3.5"><x-status-badge :status="$publication->status" /></td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('publications.show', $publication) }}" class="inline-flex items-center gap-1 font-semibold text-blue-700 hover:text-blue-900">
                            <span>Detail</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">{{ $emptyText }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($publications instanceof \Illuminate\Pagination\AbstractPaginator && $publications->hasPages())
    <div class="mt-4">{{ $publications->links() }}</div>
@endif
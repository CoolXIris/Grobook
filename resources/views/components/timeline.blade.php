@props(['events', 'revisions' => null])

@php($colors = [
    'submitted' => 'bg-blue-500',
    'content_approved' => 'bg-green-500',
    'revision_requested' => 'bg-red-500',
    'resubmitted' => 'bg-amber-500',
    'forwarded_to_final' => 'bg-purple-500',
    'final_approved' => 'bg-green-600',
])

<ol class="relative space-y-6 border-l-2 border-slate-200 pl-6">
    @foreach ($events as $event)
        <li class="relative">
            <span class="absolute -left-[31px] top-1 size-4 rounded-full border-2 border-white {{ $colors[$event->action->value] ?? 'bg-slate-400' }}"></span>
            <div class="flex flex-wrap items-baseline gap-x-2">
                <p class="font-semibold text-slate-800">{{ $event->action->label() }}</p>
                <p class="text-xs text-slate-500">{{ $event->created_at->format('d/m/Y H:i') }} WIB</p>
            </div>
            <p class="text-sm text-slate-600">
                oleh {{ $event->user?->name ?? 'Sistem' }}
                @if ($event->user)
                    <span class="text-xs text-slate-400">({{ $event->user->role->label() }})</span>
                @endif
            </p>
            @if ($event->description)
                <p class="mt-1 whitespace-pre-line rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $event->description }}</p>
            @endif
            @if (($event->metadata['revision_id'] ?? null) && $revisions?->get($event->metadata['revision_id'])?->file_path)
                <a href="{{ route('files.revision', $event->metadata['revision_id']) }}" target="_blank" class="mt-1 inline-block text-sm font-semibold text-blue-700 hover:underline">Lihat file detail revisi (PDF)</a>
            @endif
        </li>
    @endforeach
</ol>
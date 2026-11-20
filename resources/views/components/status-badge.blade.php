@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium '.$status->badgeClasses()]) }}>
    <span class="size-1.5 rounded-full current-dot bg-current opacity-75"></span>
    {{ $status->label() }}
</span>
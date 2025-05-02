@props([
    'background' => 'bg-blue-600',
    'color' => 'text-white'
])

<span class="{{ $background }} {{ $color }} truncate overflow-hidden whitespace-nowrap text-ellipsis relative text-xs font-semibold px-2.5 py-1 rounded-full block">
    {{ $slot }}
</span>

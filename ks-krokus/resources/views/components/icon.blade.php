@props(['name'])

<svg
    {{ $attributes->class(['ui-icon', 'ui-icon--'.$name]) }}
    viewBox="{{ $name === 'chevron' ? '0 0 12 8' : '0 0 24 24' }}"
    aria-hidden="true"
    focusable="false"
>
    @switch($name)
        @case('sun')
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path>
            @break

        @case('moon')
            <path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"></path>
            @break

        @case('notification')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
            <path d="M10 21h4"></path>
            @break

        @case('account')
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path>
            @break

        @case('chevron')
            <path d="m1 1 5 5 5-5"></path>
            @break

        @case('logout')
            <path d="M14 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-3"></path>
            <path d="M10 12h11M18 9l3 3-3 3"></path>
            @break

        @case('arrow-left')
            <path d="m10 17-5-5 5-5"></path>
            <path d="M5 12h14"></path>
            @break

        @case('home')
            <path d="m3 11 9-8 9 8"></path>
            <path d="M5 10v10h14V10M9 20v-6h6v6"></path>
            @break
    @endswitch
</svg>

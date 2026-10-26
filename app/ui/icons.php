<?php
declare(strict_types=1);

function icon(string $name, int $size = 19): string
{
    $paths = [
        'paw' => '<circle cx="7.5" cy="8" r="1.6"/><circle cx="16.5" cy="8" r="1.6"/><circle cx="5" cy="13" r="1.5"/><circle cx="19" cy="13" r="1.5"/><path d="M12 11c-3.1 0-6 4.1-6 6.5 0 1.6 1.3 2.5 2.8 2 2.1-.7 4.3-.7 6.4 0 1.5.5 2.8-.4 2.8-2 0-2.4-2.9-6.5-6-6.5Z"/>',
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'box' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 8 9 5 9-5M3 8v9l9 5 9-5V8M12 13v9"/>',
        'scissors' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="m8.1 8.1 12.8 12.8M14 6l-3.5 3.5M8.1 15.9 12 12"/>',
        'bag' => '<path d="M5 8h14l1 13H4L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'wallet' => '<rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 9h18M16 14h2"/>',
        'chart' => '<path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 6-7"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'close' => '<path d="m18 6-12 12M6 6l12 12"/>',
        'edit' => '<path d="m15 5 4 4M4 20l4-.8L19 8a2.8 2.8 0 0 0-4-4L4 15v5Z"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'trend' => '<path d="m3 17 6-6 4 4 8-9"/><path d="M15 6h6v6"/>',
    ];
    $body = $paths[$name] ?? $paths['paw'];
    return '<svg aria-hidden="true" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $body . '</svg>';
}

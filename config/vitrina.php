<?php

return [
    'admin_path' => trim((string) env('ADMIN_PATH', 'panel-cambiar'), '/'),

    // Search engines may index the public pages. Off until launch (PLAN F8/F9).
    'indexable' => (bool) env('VITRINA_INDEXABLE', false),

    // Private material for vitrina:import-content (outside git, PLAN §5.1).
    'content_path' => base_path('content'),

    'reserved_paths' => [
        'login', 'logout', 'admin', 'register', 'password', 'email', 'user', 'settings',
        'dashboard', 'sitemap.xml', 'robots.txt', 'build', 'storage', 'fonts', 'up',
        'forgot-password', 'reset-password', 'two-factor-challenge', 'passkeys', 'livewire',
    ],
];

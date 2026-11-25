<?php

return [
    'admin_path' => trim((string) env('ADMIN_PATH', 'panel-cambiar'), '/'),

    'reserved_paths' => [
        'login', 'logout', 'admin', 'register', 'password', 'email', 'user', 'settings',
        'dashboard', 'sitemap.xml', 'robots.txt', 'build', 'storage', 'fonts', 'up',
        'forgot-password', 'reset-password', 'two-factor-challenge', 'passkeys', 'livewire',
    ],
];

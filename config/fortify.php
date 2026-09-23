<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => false,
    'home' => '/dashboard',
    'views' => true,
    'limiters' => ['login' => 'bos-login', 'two-factor' => 'bos-two-factor'],
    'features' => [
        Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]),
    ],
];

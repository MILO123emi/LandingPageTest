<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

abstract class Controller
{
    /** Shows a toast in the admin panel after the next Inertia visit. */
    protected function toast(string $message, string $type = 'success'): void
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);
    }
}

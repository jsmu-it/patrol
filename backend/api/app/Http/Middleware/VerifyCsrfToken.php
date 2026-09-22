<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Editor dokumen menyimpan berkas lewat panggilan server-ke-server,
        // tanpa sesi maupun token CSRF; yang menjaganya tiket akses WOPI.
        'wopi/*',
        //
    ];
}

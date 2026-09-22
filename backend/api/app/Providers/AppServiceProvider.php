<?php

namespace App\Providers;

use App\Models\CmsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Menu "Our Services" mengikuti isi tabel layanan, bukan daftar tetap
        // di berkas tampilan. Dulu isinya lima tautan yang ditulis tangan dan
        // menunjuk ke penanda yang tidak pernah cocok dengan data — akibatnya
        // layanan baru tidak pernah muncul di menu, dan menu yang ada menunjuk
        // ke bagian yang tidak ada.
        View::composer('layouts.company_profile', function ($view): void {
            $view->with('menuLayanan', Cache::remember('menu_layanan', now()->addMinutes(10), function () {
                return CmsService::utama()->urut()->with('children:id,parent_id,title,slug')->get(['id', 'title', 'slug']);
            }));
        });
    }
}

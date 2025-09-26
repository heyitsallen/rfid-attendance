<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\{Section, User, YearLevel, SubjectCurriculum};

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
          View::composer('dashboards.admin', function ($view) {
        $view->with([
            'sections'   => Section::with('yearLevel')->get(),
            'faculties'  => User::where('role', 'faculty')->get(),
            'yearLevels' => YearLevel::all(),
            'subjects'   => SubjectCurriculum::all(),
        ]);

            if (config('app.url')) {
        URL::forceRootUrl(config('app.url'));   // <-- force http://192.168.50.197:8000
    }

    // If your emails somehow generate https links but your server is http:
    if (str_starts_with(config('app.url'), 'http://')) {
        URL::forceScheme('http');
    }
    });
    }
}

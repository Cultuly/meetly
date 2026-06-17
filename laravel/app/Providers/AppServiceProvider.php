<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
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
        View::composer('components.shell-layout', function ($view) {
            $workspaces = Auth::check()
                ? Auth::user()->ownedWorkspaces()->get()         
                    ->merge(Auth::user()->workspaces()->get())     
                    ->unique('id')                                 
                    ->sortBy('name')
                : collect();

            $view->with('sidebarWorkspaces', $workspaces);
        });
    }
}

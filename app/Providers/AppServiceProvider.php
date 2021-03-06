<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        // mb4string 767/4 = 191.xxx
        Schema::defaultStringLength(191);

        \View::composer('layout.sidebar', function($view){

            $topics = \App\Topic::all();

            $view->with('topics', $topics);
        });

        \DB::listen(function($query){
            $sql = $query->sql;
            $bindings = $query->bindings;
            $time = $query->time;

            if ($time > 10) {
                \Log::debug(var_export(compact('sql', 'bindings', 'time'), true));
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Post::observe(\App\Observers\ReplyObserver::class);
        \App\Zan::observe(\App\Observers\ZanObserver::class);

        // mb4string 767/4 = 191.xxx
        Schema::defaultStringLength(191);

        \View::composer('layout.sidebar', function($view){
            $topics = \App\Topic::withCount(['posts'])->orderBy('posts_count', 'desc')->offset(0)->limit(10)->get();
            $view->with('topics', $topics);
        });

        \DB::listen(function($query){
            $sql = $query->sql;
            $bindings = $query->bindings;
            $time = $query->time;

            if ($time > 10) {
                \Log::debug(var_export(compact('sql', 'bindings', 'time'), true));
            }
        });
    }
}

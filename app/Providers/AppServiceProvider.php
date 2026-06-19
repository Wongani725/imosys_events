<?php

namespace App\Providers;

use App\Mail\MicrosoftGraphTransport;
use App\Models\Bookers;
use App\Models\EvaluationSubmission;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->app->make(MailManager::class)->extend('microsoft-graph', function () {
            return new MicrosoftGraphTransport(
                config('graph.tenant_id'),
                config('graph.client_id'),
                config('graph.client_secret'),
                config('mail.from.address')
            );
        });

        Paginator::useBootstrap();

        View::composer('layouts.vertical.top-navbar', function ($view) {
            $pendingCount = Bookers::where('booking_status', 'Pending Payment')->count();
            $todayCount = Bookers::whereDate('created_at', today())->count();
            $evalCount = EvaluationSubmission::whereDate('created_at', today())->count();

            $recentBookings = Bookers::whereDate('created_at', today())
                ->latest()
                ->take(5)
                ->get();

            $view->with([
                'notificationCount' => $pendingCount + $todayCount + $evalCount,
                'recentBookings' => $recentBookings,
            ]);
        });
    }
}

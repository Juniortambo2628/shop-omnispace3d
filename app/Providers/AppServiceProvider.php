<?php

namespace App\Providers;

use App\Services\AdminOrderService;
use App\Services\AdminPackingService;
use App\Services\AdminStockService;
use App\Services\AdminUserService;
use App\Services\AuthService;
use App\Services\ImageUploadService;
use App\Services\ProductService;
use App\Services\SettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(\App\Services\OrderService::class);
        $this->app->singleton(ImageUploadService::class);
        $this->app->singleton(ProductService::class);
        $this->app->singleton(AdminOrderService::class);
        $this->app->singleton(AuthService::class);
        $this->app->singleton(AdminUserService::class);
        $this->app->singleton(AdminPackingService::class);
        $this->app->singleton(AdminStockService::class);
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Commands loaded via ContainerCommandLoader need the app instance set explicitly.
        $this->app->resolving(Command::class, function (Command $command, $app) {
            $command->setLaravel($app);
        });

        global $CONFIG;
        try {
            $settingsService = new SettingsService();
            foreach ($settingsService->loadFromDatabase() as $key => $value) {
                $CONFIG[$key] = $value;
            }
        } catch (\Exception $e) {
            // Settings table might not exist yet
        }
    }
}

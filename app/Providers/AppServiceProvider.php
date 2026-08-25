<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Custom Blade directives
        Blade::directive('money', function ($amount) {
            return "<?php echo '₹' . number_format((float)($amount), 2); ?>";
        });

        Blade::directive('dateformat', function ($date) {
            return "<?php echo ($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '-'; ?>";
        });

        Blade::directive('datetimeformat', function ($date) {
            return "<?php echo ($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y h:i A') : '-'; ?>";
        });
    }
}

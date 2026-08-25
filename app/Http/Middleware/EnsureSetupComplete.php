<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SetupWizard;

class EnsureSetupComplete
{
    public function handle(Request $request, Closure $next)
    {
        // Skip for setup routes, assets, and login
        if ($request->is('setup*') || $request->is('assets/*') || $request->is('_debugbar/*')) {
            return $next($request);
        }

        if (!SetupWizard::isCompleted()) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}

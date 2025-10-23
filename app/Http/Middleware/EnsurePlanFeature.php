<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnsurePlanFeature
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        if (!Gate::allows('access-plan-feature', $feature)) {
            abort(403, __('This feature is not available in your current plan.'));
        }

        return $next($request);
    }
}

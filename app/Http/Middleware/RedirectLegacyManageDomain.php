<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyManageDomain
{
    /**
     * Optional redirect for a retired hostname. Enforced in the app itself
     * (not just Apache) so a server/control-panel config reset cannot
     * silently re-expose the old host. Inactive unless LEGACY_MANAGE_HOST
     * and LEGACY_MANAGE_TARGET are set in .env.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $legacy = config('app.legacy_manage_host');
        $target = config('app.legacy_manage_target');

        if ($legacy && $target) {
            $host = $request->getHost();
            if ($host === $legacy || $host === 'www.' . $legacy) {
                return redirect($target, 301);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\WorkspaceSessionManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveWorkspaceSession
{
    public function __construct(private WorkspaceSessionManager $workspaces)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->route('workspace');
        $activeWorkspaces = $this->workspaces->present($request);
        $currentWorkspaceKey = null;

        if (is_string($workspace) && $workspace !== '') {
            $user = $this->workspaces->userFor($request, $workspace);

            if (!$user) {
                if ($fallbackWorkspace = $this->workspaces->lastWorkspaceKey($request)) {
                    return redirect()
                        ->route('workspace.dashboard', ['workspace' => $fallbackWorkspace])
                        ->with('error', 'That workspace session is no longer active.');
                }

                return redirect()->route('login')->with('error', 'Please login again to continue.');
            }

            Auth::guard('web')->setUser($user);
            $request->setUserResolver(static fn () => $user);

            $this->workspaces->touch($request, $workspace);
            $activeWorkspaces = $this->workspaces->present($request);
            $currentWorkspaceKey = $workspace;
        } elseif ($fallbackWorkspace = $this->workspaces->lastWorkspaceKey($request)) {
            if ($request->isMethod('get') || $request->isMethod('head')) {
                return $this->redirectToWorkspaceRoute($request, $fallbackWorkspace);
            }

            return redirect()
                ->route('workspace.dashboard', ['workspace' => $fallbackWorkspace])
                ->with('error', 'Please continue from one of your active workspace tabs.');
        } elseif (Auth::guard('web')->check()) {
            $request->setUserResolver(static fn () => Auth::guard('web')->user());
        } else {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        View::share('activeWorkspaces', $activeWorkspaces);
        View::share('currentWorkspaceKey', $currentWorkspaceKey);

        return $next($request);
    }

    private function redirectToWorkspaceRoute(Request $request, string $workspace): Response
    {
        $route = $request->route();
        $routeName = $route?->getName();

        if (!$routeName) {
            return redirect()->route('workspace.dashboard', ['workspace' => $workspace]);
        }

        $parameters = $route->parameters();
        unset($parameters['workspace']);
        $workspaceRouteName = 'workspace.' . $routeName;

        if (Route::has($workspaceRouteName)) {
            $parameters['workspace'] = $workspace;

            return redirect()->route($workspaceRouteName, $parameters);
        }

        return redirect()->route($routeName, $parameters);
    }
}

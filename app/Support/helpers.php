<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('workspace_route')) {
    function workspace_route(string $name, $parameters = [], bool $absolute = true): string
    {
        $workspace = null;

        if (is_array($parameters) && isset($parameters['workspace'])) {
            $workspace = $parameters['workspace'];
        } elseif (Route::current()) {
            $workspace = Route::current()->parameter('workspace');
        }

        if ($workspace && Route::has('workspace.' . $name)) {
            $workspaceParameters = is_array($parameters)
                ? array_merge(['workspace' => $workspace], $parameters)
                : ['workspace' => $workspace, $parameters];

            return route('workspace.' . $name, $workspaceParameters, $absolute);
        }

        return route($name, $parameters, $absolute);
    }
}

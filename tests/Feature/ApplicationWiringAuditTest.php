<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class ApplicationWiringAuditTest extends TestCase
{
    public function test_every_controller_route_points_to_an_existing_public_method(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if ($action === 'Closure') {
                continue;
            }

            [$class, $method] = str_contains($action, '@')
                ? explode('@', $action, 2)
                : [$action, '__invoke'];

            $this->assertTrue(class_exists($class), "Route [{$route->uri()}] references missing controller {$class}.");
            $this->assertTrue(method_exists($class, $method), "Route [{$route->uri()}] references missing method {$class}@{$method}.");
            $this->assertTrue((new ReflectionMethod($class, $method))->isPublic(), "Route [{$route->uri()}] references non-public method {$class}@{$method}.");
        }
    }

    public function test_controller_views_and_literal_blade_routes_exist(): void
    {
        foreach (File::allFiles(app_path('Http/Controllers')) as $file) {
            preg_match_all('/view\(\s*[\'\"]([^\'\"]+)/', $file->getContents(), $matches);
            foreach ($matches[1] as $view) {
                $this->assertTrue(view()->exists($view), "Controller view [{$view}] does not exist ({$file->getRelativePathname()}).");
            }
        }

        foreach (File::allFiles(resource_path('views')) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            preg_match_all('/(?<!->)\broute\(\s*[\'\"]([^\'\"]+)/', $file->getContents(), $matches);
            foreach (array_unique($matches[1]) as $routeName) {
                $this->assertTrue(Route::has($routeName), "Blade route [{$routeName}] does not exist ({$file->getRelativePathname()}).");
            }
        }
    }
}

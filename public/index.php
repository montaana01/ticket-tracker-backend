<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TicketTracker\Controllers\AuthController;
use TicketTracker\Middleware\AuthMiddleware;
use TicketTracker\Controllers\UserController;
use TicketTracker\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
if (empty($uri)) $uri = '/';

$httpMethod = $_SERVER['REQUEST_METHOD'];

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $router) {
    $router->addRoute('POST', '/api/register', [AuthController::class, 'register']);
    $router->addRoute('POST', '/api/login', [AuthController::class, 'login']);
    $router->addRoute('GET', '/api/profile', [UserController::class, 'getProfile']);
    $router->addRoute('GET', '/api/admin/users/{id:\d+}', [UserController::class, 'getUserById']);
    $router->addRoute('GET', '/api/admin/users', [UserController::class, 'getUsers']);
});

$routeStatus = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeStatus[0]) {
    case FastRoute\Dispatcher::FOUND:
        [$controllerClass, $method] = $routeStatus[1];
        $vars = $routeStatus[2] ?? [];

        $authRequirements = [
            '/api/register' => null,
            '/api/login' => null,
            '/api/profile' => ['user', 'admin'],
            '/api/admin/users' => ['admin'],
            '/api/admin/users/{id}' => ['admin'],
        ];

        $requiredRoles = $authRequirements[$uri];

        if (is_null($requiredRoles)) {
            $controller = new $controllerClass();
            $controller->$method(...array_values($vars));
        } else {
            $authMiddleware = new AuthMiddleware($requiredRoles);
            $user = $authMiddleware->handle();
            $controller = new $controllerClass();
            $controller->$method($user, ...array_values($vars));
        }
        break;

    case FastRoute\Dispatcher::NOT_FOUND:
        Response::json(['error' => 'Route not found', 'uri' => $uri], 404);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeStatus[1];
        Response::json([
            'error' => 'Method not allowed',
            'requested_method' => $httpMethod,
            'allowed_methods' => $allowedMethods,
            'uri' => $uri
        ], 405);
        break;
}
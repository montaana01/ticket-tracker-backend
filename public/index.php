<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TicketTracker\Controllers\AuthController;
use TicketTracker\Controllers\StatusesController;
use TicketTracker\Controllers\TagsController;
use TicketTracker\Middleware\AuthMiddleware;
use TicketTracker\Controllers\UserController;
use TicketTracker\Controllers\TicketController;
use TicketTracker\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
if (empty($uri)) $uri = '/';

$httpMethod = $_SERVER['REQUEST_METHOD'];

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $router) {
    $router->addRoute('POST', '/auth/sign-up', [AuthController::class, 'signUp']);
    $router->addRoute('POST', '/auth/sign-in', [AuthController::class, 'signIn']);
    //Get user info
    $router->addRoute('GET', '/api/user/profile', [UserController::class, 'getProfile']);
    //Ticket paths
    $router->addRoute('GET', '/api/tickets', [TicketController::class, 'getTickets']);
    $router->addRoute('GET', '/api/ticket/{id:\d+}', [TicketController::class, 'getTicket']);
    //Create ticket
    $router->addRoute('POST', '/api/tickets', [TicketController::class, 'createTicket']);

    //Admins paths:
    $router->addRoute('GET', '/api/admin/users', [UserController::class, 'getUsers']);
    $router->addRoute('GET', '/api/admin/user/{id:\d+}', [UserController::class, 'getUserById']);
    $router->addRoute('GET', '/api/admin/statuses', [StatusesController::class, 'getStatuses']);
    $router->addRoute('GET', '/api/admin/tags', [TagsController::class, 'getTags']);
    //Update ticket data
    $router->addRoute('PUT', '/api/ticket/{id:\d+}/status', [TicketController::class, 'updateStatus']);
    $router->addRoute('PUT', '/api/ticket/{id:\d+}/tag', [TicketController::class, 'updateTag']);
});

$routeStatus = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeStatus[0]) {
    case FastRoute\Dispatcher::FOUND:
        [$controllerClass, $method] = $routeStatus[1];
        $vars = $routeStatus[2] ?? [];

        $authRequirements = [
            '/auth/sign-up' => null,
            '/auth/sign-in' => null,
            '/api/user/profile' => ['user', 'admin'],
            '/api/tickets' => ['user', 'admin'],
            '/api/ticket/{id}' => ['user', 'admin'],
            '/user/ticket' => ['user'],
            '/api/admin/users' => ['admin'],
            '/api/admin/user/{id}' => ['admin'],
            '/api/admin/statuses' => ['admin'],
            '/api/admin/tags' => ['admin'],
            '/api/ticket/{id}/status' => ['admin'],
            '/api/ticket/{id}/tag' => ['admin'],
        ];

        $requiredRoles = null;
        foreach ($authRequirements as $pattern => $roles) {
            $regexp = '#^' . preg_replace('#\{[^/]+}#', '[^/]+', $pattern) . '$#';
            if (preg_match($regexp, $uri)) {
                $requiredRoles = $roles;
                break;
            }
        }

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

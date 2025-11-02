<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TicketTracker\Controllers\AuthController;
use TicketTracker\Controllers\StatusesController;
use TicketTracker\Controllers\TagsController;
use TicketTracker\Middleware\AuthMiddleware;
use TicketTracker\Controllers\UserController;
use TicketTracker\Controllers\TicketController;
use TicketTracker\Helpers\Response;

$allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:8081',
    'http://localhost:8080',
    'http://localhost:8888',
    'https://tickets.yakovlevdev.com',
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
}

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
    $router->addRoute('POST', '/auth/sign-out', [AuthController::class, 'signOut']);
    $router->addRoute('GET', '/auth/check', [AuthController::class, 'checkAuth']);
    //Get user info
    $router->addRoute('GET', '/api/user/profile', [UserController::class, 'getProfile']);
    //Ticket paths
    $router->addRoute('GET', '/api/tickets', [TicketController::class, 'getTickets']);
    $router->addRoute('GET', '/api/ticket/{id:\d+}', [TicketController::class, 'getTicket']);
    $router->addRoute('DELETE', '/api/ticket/{id:\d+}', [TicketController::class, 'removeTicket']);
    //Create ticket
    $router->addRoute('POST', '/api/tickets', [TicketController::class, 'createTicket']);
    //Get tags
    $router->addRoute('GET', '/api/tags', [TagsController::class, 'getTags']);
    //Get statuses
    $router->addRoute('GET', '/api/statuses', [StatusesController::class, 'getStatuses']);

    //Admins paths:
    $router->addRoute('GET', '/api/admin/users', [UserController::class, 'getUsers']);
    $router->addRoute('GET', '/api/admin/user/{id:\d+}', [UserController::class, 'getUserById']);
    //CRUD tags && statuses
    $router->addRoute('POST', '/api/admin/tags', [TagsController::class, 'createTag']);
    $router->addRoute('PUT', '/api/admin/tags/{id:\d+}', [TagsController::class, 'updateTag']);
    $router->addRoute('DELETE', '/api/admin/tags/{id:\d+}', [TagsController::class, 'removeTag']);
    $router->addRoute('POST', '/api/admin/statuses', [StatusesController::class, 'createStatus']);
    $router->addRoute('PUT', '/api/admin/statuses/{id:\d+}', [StatusesController::class, 'updateStatus']);
    $router->addRoute('DELETE', '/api/admin/statuses/{id:\d+}', [StatusesController::class, 'removeStatus']);
    //Update ticket data
    $router->addRoute('PUT', '/api/admin/ticket/{id:\d+}/status', [TicketController::class, 'updateStatus']);
    $router->addRoute('PUT', '/api/admin/ticket/{id:\d+}/tag', [TicketController::class, 'updateTag']);
    //Add message
    $router->addRoute('POST', '/api/admin/ticket/{id:\d+}/message', [TicketController::class, 'addMessage']);
});

$routeStatus = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeStatus[0]) {
    case FastRoute\Dispatcher::FOUND:
        [$controllerClass, $method] = $routeStatus[1];
        $vars = $routeStatus[2] ?? [];

        $authRequirements = [
            '/auth/sign-up' => null,
            '/auth/sign-in' => null,
            '/auth/check' => null,
            '/auth/sign-out' => ['user', 'admin'],
            '/api/user/profile' => ['user', 'admin'],
            '/api/tickets' => ['user', 'admin'],
            '/api/ticket/{id}' => ['user', 'admin'],
            '/user/ticket' => ['user'],
            '/api/tags' => ['user', 'admin'],
            '/api/statuses' => ['user', 'admin'],
            '/api/admin/users' => ['admin'],
            '/api/admin/user/{id}' => ['admin'],
            '/api/admin/tags' => ['admin'],
            '/api/admin/tags/{id}' => ['admin'],
            '/api/admin/statuses' => ['admin'],
            '/api/admin/statuses/{id}' => ['admin'],
            '/api/admin/ticket/{id}/status' => ['admin'],
            '/api/admin/ticket/{id}/tag' => ['admin'],
            '/api/admin/ticket/{id}/message' => ['admin'],
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
        Response::json([
            'error' => 'Route not found',
            'message' => 'No matching route for URI: ' . $uri
        ], 404);
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeStatus[1];
        Response::json([
            'error' => 'Method not allowed',
            'message' =>[
                'requested_method' => $httpMethod,
                'allowed_methods' => $allowedMethods,
            ],
            'uri' => $uri
        ], 405);
        break;
}

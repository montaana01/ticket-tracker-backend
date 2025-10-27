<?php

namespace TicketTracker\Middleware;

use TicketTracker\Helpers\JwtAuth;
use TicketTracker\Helpers\Response;

class AuthMiddleware
{
    private JwtAuth $jwtAuth;
    private ?array $requiredRoles;

    public function __construct(array $requiredRoles = null)
    {
        $this->jwtAuth = new JwtAuth();
        $this->requiredRoles = $requiredRoles;
    }

    public function handle(): object
    {
        $token = $this->getToken();

        if (!$token) {
            Response::json(['error' => 'Token required'], 401);
            exit;
        }

        $user = $this->jwtAuth->validateToken($token);

        if (!$user) {
            Response::json(['error' => 'Invalid or expired token'], 401);
            exit;
        }

        if ($this->requiredRoles && !in_array($user->role, $this->requiredRoles)) {
            Response::json(['error' => 'Insufficient permissions. Required roles: ' . implode(', ', $this->requiredRoles)], 403);
            exit;
        }

        return $user;
    }


    private function getToken(): ?string
    {
        if (!empty($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        return null;
    }
}
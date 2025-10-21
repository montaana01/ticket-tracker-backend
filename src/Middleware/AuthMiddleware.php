<?php

namespace TicketTracker\Middleware;

use TicketTracker\Helpers\JwtAuth;

class AuthMiddleware
{
    private JwtAuth $jwtAuth;

    public function __construct()
    {
        $this->jwtAuth = new JwtAuth();
    }

    public function handle(): ?object
    {
        $token = $this->getTokenFromHeader();

        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token required'], JSON_THROW_ON_ERROR);
            exit;
        }

        return $this->jwtAuth->validateToken($token);
    }

    private function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }
        return null;
    }
}
<?php

namespace TicketTracker\Helpers;

use TicketTracker\Helpers\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth
{
    private string $token;

    public function __construct()
    {
//        if ($_ENV['JWT_SECRET']) {
//            $this->token = $_ENV['JWT_SECRET'];
//        } else {
        try {
            $params = require __DIR__ . '/../Config/params.php';
        } catch (\Exception $error) {
            Response::json(['error' => 'Configuration file does not exist: ' . $error], 400);
        }
        $this->token = $params['JWT'];
//        }
    }

    public function generateToken(int $userId, string $role, int $ttlHours = 24): string
    {
        $issuedAt = time();
        $payload = [
            'iss' => 'TicketTracker',
            'sub' => $userId,
            'role' => $role,
            'iat' => $issuedAt,
            'exp' => $issuedAt + ($ttlHours * 3600)
        ];
        return JWT::encode($payload, $this->token, 'HS256');
    }

    public function validateToken(string $token): \stdClass|array
    {
        try {
            return JWT::decode($token, new Key($this->token, 'HS256'));
        } catch (\Exception $error) {
            return Response::json(['error' => $error->getMessage()], 400);
        }
    }
}
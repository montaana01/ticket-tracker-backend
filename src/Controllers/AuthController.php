<?php

namespace TicketTracker\Controllers;

use TicketTracker\Helpers\Response;
use TicketTracker\Models\UserModel;
use TicketTracker\Controllers\UserController;
use TicketTracker\Helpers\JwtAuth;

class AuthController
{
    private $JwtAuth;
    private $userModel;
    private $userController;

    public function __construct()
    {
        $this->JwtAuth = new JwtAuth();
        $this->userModel = new UserModel();
        $this->userController = new UserController();
    }

    public function register(): void
    {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        if (empty($data['username']) || empty($data['password'])) {
            Response::json(['error' => 'Invalid registration data!'], 400);
        }

        if ($this->userModel->getByUsername($data['Username'])) {
            Response::json(['error' => 'Username already exists'], 409);
        }

        $this->userController->create($data['Username'], $data['password']);
        Response::json(['message' => 'User created']);
    }

    public function login(): void
    {
        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        $user = $this->userModel->getByUsername($data['Username'] ?? '');
        if (!$user || !password_verify($data['password'] ?? '', $user['password_hash'])) {
            Response::json(['error' => 'Invalid credentials'], 401);
        }

        $token = $this->JwtAuth->generateToken($user['id'], $user['role']);
        Response::json(['token' => $token, 'role' => $user['role']]);
    }
}
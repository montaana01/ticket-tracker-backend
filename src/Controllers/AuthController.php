<?php

namespace TicketTracker\Controllers;

use Exception;
use JsonException;
use TicketTracker\Helpers\Response;
use TicketTracker\Models\UserModel;
use TicketTracker\Helpers\JwtAuth;

class AuthController
{
    private JwtAuth $JwtAuth;
    private UserModel $userModel;
    private UserController $userController;

    public function __construct()
    {
        $this->JwtAuth = new JwtAuth();
        $this->userModel = new UserModel();
        $this->userController = new UserController();
    }

    public function signUp(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

            if (!$data || empty($data['username']) || empty($data['password']) || !$data['username'] || !$data['password']) {
                Response::json(['error' => 'Invalid registration data!'], 400);
            }

            if ($this->userModel->getByUsername($data['username'])) {
                Response::json(['error' => 'Username already exists'], 409);
            }

            $response = $this->userController->create($data['username'], $data['password']);
            if (!$response['success']) {
                Response::json(['error' => 'User creation failed: ' . $response['error']], 500);
                return;
            }

            Response::json([
                'data' => ['id' => $response['user_id']],
                'message' => $response['message']
            ]);
        } catch (JsonException $exception) {
            Response::json([
                'error' => 'Invalid JSON format',
                'message' => 'JSON parsing error: ' . $exception->getMessage()
            ], 400);
            return;
        }
    }

    public function signIn(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
            $user = $this->userModel->getByUsername($data['username'] ?? '');

            if (!$user) {
                Response::json(['error' => 'User does not exist'], 401);
            }

            if (!password_verify($data['password'] ?? '', $user['password_hash'])) {
                Response::json(['error' => 'Password or Username not correct'], 401);
            }

            $token = $this->JwtAuth->generateToken($user['id'], $user['role']);
            setcookie(
                "auth_token",
                $token,
                [
                    'expires' => time() + 3600 * 24 * 30,
                    "httponly" => true,
                    "secure" => true,
                    "samesite" => "None",
                    "domain" => ".yakovlevdev.com",
                    "path" => "/",
                ]
            );
            Response::json(['data' => ['role' => $user['role']]]);
        } catch (JsonException $exception) {
            Response::json([
                'error' => 'Invalid JSON format',
                'message' => 'JSON parsing error: ' . $exception->getMessage()
            ], 400);
            return;
        }
    }

    public function signOut(): void
    {
        setcookie(
            "auth_token",
            "",
            [
                'expires' => time() - 3600,
                "httponly" => true,
                "secure" => true,
                "samesite" => "None",
                "domain" => ".yakovlevdev.com",
                "path" => "/",
            ]
        );

        Response::json(['message' => 'Successfully signed out']);
    }

    public function checkAuth(): void
    {
        $token = $_COOKIE['auth_token'] ?? '';

        if (!$token) {
            Response::json(['data' => ['authenticated' => false]]);
            return;
        }

        try {
            $payload = $this->JwtAuth->validateToken($token);
            $user = $this->userModel->get($payload->user);
            if (!$user) {
                Response::json(['data' => ['authenticated' => false]]);
                return;
            }

            Response::json(['data' => [
                'authenticated' => true,
                'role' => $user['role']
            ]]);
        } catch (Exception $error) {
            Response::json(['data' => ['authenticated' => false], 'message' => $error->getMessage()]);
        }
    }
}
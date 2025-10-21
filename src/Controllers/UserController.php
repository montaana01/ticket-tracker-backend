<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\UserModel;

class UserController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function create(string $username, string $password): array
    {
        try {
            $userData = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userModel->create($userData);

            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'User created successfully'
            ];

        } catch (\Exception $error) {
            return [
                'success' => false,
                'error' => $error->getMessage()
            ];
        }
    }


    public function getUsers(): void
    {
        try {
            $users = $this->userModel->getAll();
            header('Content-Type: application/json');
            echo json_encode($users, JSON_THROW_ON_ERROR);
        } catch (\Exception $error) {
            http_response_code(500);
            echo "An error occurred: " . $error->getMessage();
        }
    }

    public function verifyPassedData(string $username, string $password): ?array
    {
        $user = $this->userModel->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return null;
    }
}
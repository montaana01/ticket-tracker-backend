<?php

namespace TicketTracker\Controllers;

use TicketTracker\Helpers\Response;
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
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
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

    public function getProfile(object $user): void
    {
        try {
            $userId = $user->sub;
            $userData = $this->userModel->get($userId);

            if (!$userData) {
                Response::json(['error' => 'User not found'], 404);
                return;
            }
            unset($userData['password_hash']);

            Response::json([
                'success' => true,
                'data' => $userData
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get profile'], 500);
        }
    }

    public function getUsers(): void
    {
        try {
            $users = $this->userModel->getAll();
            $users = array_map(static function ($userData) {
                unset($userData['password']);
                return $userData;
            }, $users);
            Response::json(['success' => true, 'data' => $users]);
        } catch (\Exception $error) {
            Response::json(['success' => false, 'message' => "An error occurred: " . $error->getMessage()], 500);
        }
    }

    public function getUserById(int $id): void
    {
        try {
            $userData = $this->userModel->get($id);
            if (!$userData) {
                Response::json(['error' => 'User not found'], 404);
                return;
            }
            unset($userData['password_hash']);
            Response::json([
                'success' => true,
                'data' => $userData
            ]);
        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get user'], 500);
        }
    }
}
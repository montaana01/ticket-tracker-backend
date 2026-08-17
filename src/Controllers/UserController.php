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

    /** @return array{success: true, user_id: int, message: string} */
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
            Response::json(['error' => 'An error occurred: ' . $error->getMessage()], 500);
        }
    }

    public function getProfile(\stdClass $user): never
    {
        try {
            $userId = $user->user;
            $userData = $this->userModel->get($userId);

            if (!$userData) {
                Response::json(['error' => 'User not found'], 404);
            }
            unset($userData['password_hash']);

            Response::json([
                'data' => $userData
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get profile: ' . $error->getMessage()], 500);
        }
    }

    public function getUsers(): void
    {
        try {
            $users = $this->userModel->getAll();
            $users = array_map(static function (array $userData): array {
                unset($userData['password_hash']);
                return $userData;
            }, $users);
            Response::json(['data' => $users]);
        } catch (\Exception $error) {
            Response::json(['error' => 'An error occurred: ' . $error->getMessage()], 500);
        }
    }

    public function getUserById(\stdClass $user, string $id): never
    {
        try {
            if ($user->role === 'user') {
                Response::json(['error' => 'Access denied'], 403);
            }
            $userData = $this->userModel->get((int) $id);
            if (!$userData) {
                Response::json(['error' => 'User not found'], 404);
            }
            unset($userData['password_hash']);
            Response::json([
                'data' => $userData
            ]);
        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get user: ' . $error->getMessage()], 500);
        }
    }
}

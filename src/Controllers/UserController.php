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
}
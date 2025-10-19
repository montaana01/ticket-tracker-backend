<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TicketTracker\Controllers\UserController;

header('Content-Type: application/json');
// Basic workpiece with getting basic users info from db
try {
    $userController = new UserController();
    $userController->getUsers();
} catch (Exception $error) {
    http_response_code(500);
    echo "An error occurred: " . $error->getMessage();
}
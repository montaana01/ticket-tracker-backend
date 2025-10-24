<?php
namespace TicketTracker\Helpers;

class Response {
    public static function json($data, int $status = 200): array {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

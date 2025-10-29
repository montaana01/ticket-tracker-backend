<?php
namespace TicketTracker\Helpers;

class Response {
    public static function json($data, int $status = 200): array
    {
        http_response_code($status);
        header('Content-Type: application/json');

        $response = [
            'success' => !isset($data['error']),
            'data' => $data['data'] ?? null,
            'message' => $data['message'] ?? null,
            'error' => $data['error'] ?? null,
        ];
        echo json_encode($response, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

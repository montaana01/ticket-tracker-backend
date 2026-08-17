<?php
namespace TicketTracker\Helpers;

class Response {
    /** @param array{data?: mixed, message?: mixed, error?: mixed} $data */
    public static function json(array $data, int $status = 200): never
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

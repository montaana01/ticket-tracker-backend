<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\StatusesModel;
use TicketTracker\Helpers\Response;

class StatusesController
{
    private StatusesModel $statusesModel;

    public function __construct()
    {
        $this->statusesModel = new StatusesModel();
    }

    public function getStatuses()
    {
        try {
            $statuses = $this->statusesModel->getAll();

            return Response::json([
                'data' => $statuses,
                'message' => 'Statuses retrieved successfully'
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to get statuses'], 500);
        }
    }


    public function createStatus()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($body['name'])) {
            return Response::json(['error' => 'Name required'], 400);
        }

        try {
            $id = $this->statusesModel->create(['name' => $body['name']]);
            $tag = $this->statusesModel->get($id);
            return Response::json(['data' => $tag], 201);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Failed to create status'], 500);
        }
    }

    public function updateStatus($id)
    {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($body['name'])) {
            return Response::json(['error' => 'Name required'], 400);
        }

        try {
            $ok = $this->statusesModel->update((int)$id, ['name' => $body['name']]);
            if (!$ok) return Response::json(['error' => 'Update failed'], 500);
            $tag = $this->statusesModel->get((int)$id);
            return Response::json(['data' => $tag]);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Failed to update status'], 500);
        }
    }
}
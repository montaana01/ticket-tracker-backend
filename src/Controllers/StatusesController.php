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

    public function getStatuses(): never
    {
        try {
            $statuses = $this->statusesModel->getAll();

            Response::json([
                'data' => $statuses,
                'message' => 'Statuses retrieved successfully'
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get statuses'], 500);
        }
    }


    public function createStatus(): never
    {
        $body = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
        if (empty($body['name'])) {
            Response::json(['error' => 'Name required'], 400);
        }

        try {
            $id = $this->statusesModel->create(['name' => $body['name']]);
            $tag = $this->statusesModel->get($id);
            Response::json(['data' => $tag], 201);
        } catch (\Exception $e) {
            Response::json(['error' => 'Failed to create status'], 500);
        }
    }

    public function updateStatus(\stdClass $user, string $id): never
    {
        try {
            $updatedStatus = $this->statusesModel->get((int) $id);
            if (!$updatedStatus) {
                Response::json(['error' => 'Status not found'], 404);
            }

            if ($user->role === 'user') {
                Response::json(['error' => 'Access denied'], 403);
            }
            $body = json_decode(file_get_contents('php://input') ?: '', true) ?: [];

            if (empty($body['name'])) {
                Response::json(['error' => 'Name required'], 400);
            }

            if ($updatedStatus['name'] === $body['name']) {
                Response::json(['error' => 'Status name already exists'], 409);
            }

            $preparedData = ['name' => $body['name']];
            $update = $this->statusesModel->update((int)$id, $preparedData);

            if (!$update) {
                Response::json(['error' => 'Update failed'], 400);
            }

            $status = $this->statusesModel->get((int) $id);
            Response::json(['data' => $status]);
        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to update status: '.$error], 500);
        }
    }

    public function removeStatus(\stdClass $user, string $id): never
    {
        try {
            $deletedStatus = $this->statusesModel->get((int) $id);
            if (!$deletedStatus) {
                Response::json(['error' => 'Status not found'], 404);
            }

            if ($user->role === 'user') {
                Response::json(['error' => 'Access denied'], 403);
            }

            $this->statusesModel->delete((int) $id);
            Response::json([
                'message' => 'Status deleted successfully'
            ], 204);

        } catch (\Exception $error) {
            if ($error->getCode() === '23000') {
                Response::json(['error' => 'Cannot delete: status is in use'], 409);
            }
            Response::json(['error' => 'Failed to remove status: '.$error], 500);
        }
    }
}
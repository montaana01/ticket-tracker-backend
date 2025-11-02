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

    public function updateStatus($user, $id)
    {
        try {
            $updatedStatus = $this->statusesModel->get($id);
            if (!$updatedStatus) {
                return Response::json(['error' => 'Status not found'], 404);
            }

            if ($user->role === 'user') {
                return Response::json(['error' => 'Access denied'], 403);
            }
            $body = json_decode(file_get_contents('php://input'), true) ?: [];

            if (empty($body['name'])) {
                return Response::json(['error' => 'Name required'], 400);
            }

            if ($updatedStatus === $body['name']) {
                return Response::json(['error' => 'Status name already exists'], 409);
            }

            $preparedData = ['name' => $body['name']];
            $update = $this->statusesModel->update((int)$id, $preparedData);

            if (!$update) {
                return Response::json(['error' => 'Update failed'], 400);
            }

            $status = $this->statusesModel->get($id);
            return Response::json(['data' => $status]);
        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to update status: '.$error], 500);
        }
    }

    public function removeStatus($user, $id)
    {
        try {
            $deletedStatus = $this->statusesModel->get($id);
            if (!$deletedStatus) {
                return Response::json(['error' => 'Status not found'], 404);
            }

            if ($user->role === 'user') {
                return Response::json(['error' => 'Access denied'], 403);
            }

            $this->statusesModel->delete($id);
            return Response::json([
                'message' => 'Status deleted successfully'
            ], 204);

        } catch (\Exception $error) {
            if ($error->getCode() === '23000') {
                return Response::json(['error' => 'Cannot delete: status is in use'], 409);
            }
            return Response::json(['error' => 'Failed to remove status: '.$error], 500);
        }
    }
}
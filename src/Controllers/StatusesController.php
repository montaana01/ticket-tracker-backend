<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\StatusesModel;

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
                'success' => true,
                'data' => $statuses
            ], 201);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to get statuses'], 500);
        }
    }
}
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
}
<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\TagsModel;

class TagsController
{
    private TagsModel $tagsModel;

    public function __construct()
    {
        $this->tagsModel = new TagsModel();
    }

    public function getTags()
    {
        try {
            $statuses = $this->tagsModel->getAll();

            return Response::json([
                'success' => true,
                'data' => $statuses
            ], 201);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to get tags'], 500);
        }
    }
}
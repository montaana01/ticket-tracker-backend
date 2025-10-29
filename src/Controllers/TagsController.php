<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\TagsModel;
use TicketTracker\Helpers\Response;

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
            $tags = $this->tagsModel->getAll();

            return Response::json([
                'data' => $tags,
                'message' => 'Tags retrieved successfully'
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to get tags'], 500);
        }
    }
}
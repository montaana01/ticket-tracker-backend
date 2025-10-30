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

    public function createTag()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($body['name'])) {
            return Response::json(['error' => 'Name required'], 400);
        }

        try {
            $id = $this->tagsModel->create(['name' => $body['name']]);
            $tag = $this->tagsModel->get($id);
            return Response::json(['data' => $tag], 201);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Failed to create tag'], 500);
        }
    }

    public function updateTag($id)
    {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        if (empty($body['name'])) {
            return Response::json(['error' => 'Name required'], 400);
        }

        try {
            $ok = $this->tagsModel->update((int)$id, ['name' => $body['name']]);
            if (!$ok) return Response::json(['error' => 'Update failed'], 500);
            $tag = $this->tagsModel->get((int)$id);
            return Response::json(['data' => $tag]);
        } catch (\Exception $e) {
            return Response::json(['error' => 'Failed to update tag'], 500);
        }
    }
}

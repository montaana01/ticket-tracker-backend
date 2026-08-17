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

    public function getTags(): never
    {
        try {
            $tags = $this->tagsModel->getAll();

            Response::json([
                'data' => $tags,
                'message' => 'Tags retrieved successfully'
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get tags'], 500);
        }
    }

    public function createTag(): never
    {
        $body = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
        if (empty($body['name'])) {
            Response::json(['error' => 'Name required'], 400);
        }

        try {
            $id = $this->tagsModel->create(['name' => $body['name']]);
            $tag = $this->tagsModel->get($id);
            Response::json(['data' => $tag], 201);
        } catch (\Exception $e) {
            Response::json(['error' => 'Failed to create tag'], 500);
        }
    }

    public function updateTag(\stdClass $user, string $id): never
    {
        try {
            $updatedTag = $this->tagsModel->get((int) $id);
            if (!$updatedTag) {
                Response::json(['error' => 'Tag not found'], 404);
            }

            if ($user->role === 'user') {
                Response::json(['error' => 'Access denied'], 403);
            }
            $body = json_decode(file_get_contents('php://input') ?: '', true) ?: [];

            if (empty($body['name'])) {
                Response::json(['error' => 'Name required'], 400);
            }

            if ($updatedTag['name'] === $body['name']) {
                Response::json(['error' => 'Status name already exists'], 409);
            }

            $preparedData = ['name' => $body['name']];
            $update  = $this->tagsModel->update((int)$id, $preparedData);

            if (!$update) {
                Response::json(['error' => 'Update failed'], 500);
            }

            $tag = $this->tagsModel->get((int)$id);
            Response::json(['data' => $tag]);
        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to update tag: '.$error], 500);
        }
    }

    public function removeTag(\stdClass $user, string $id): never
    {
        try {
            $deletedTag = $this->tagsModel->get((int) $id);
            if (!$deletedTag) {
                Response::json(['error' => 'Tag not found'], 404);
            }

            if ($user->role === 'user') {
                Response::json(['error' => 'Access denied'], 403);
            }

            $this->tagsModel->delete((int) $id);
            Response::json([
                'message' => 'Tag deleted successfully'
            ], 204);

        } catch (\Exception $error) {
            if ($error->getCode() === '23000') {
                Response::json(['error' => 'Cannot delete: tag is in use'], 409);
            }
            Response::json(['error' => 'Failed to remove tag: '.$error], 500);
        }
    }
}
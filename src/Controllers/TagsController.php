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

    public function updateTag($user, $id)
    {
        try {
            $updatedTag = $this->tagsModel->get($id);
            if (!$updatedTag) {
                return Response::json(['error' => 'Tag not found'], 404);
            }

            if ($user->role === 'user') {
                return Response::json(['error' => 'Access denied'], 403);
            }
            $body = json_decode(file_get_contents('php://input'), true) ?: [];

            if (empty($body['name'])) {
                return Response::json(['error' => 'Name required'], 400);
            }

            if ($updatedTag === $body['name']) {
                return Response::json(['error' => 'Status name already exists'], 409);
            }

            $preparedData = ['name' => $body['name']];
            $update  = $this->tagsModel->update((int)$id, $preparedData);

            if (!$update) {
                return Response::json(['error' => 'Update failed'], 500);
            }

            $tag = $this->tagsModel->get((int)$id);
            return Response::json(['data' => $tag]);
        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to update tag: '.$error], 500);
        }
    }

    public function removeTag($user, $id)
    {
        try {
            $deletedTag = $this->tagsModel->get($id);
            if (!$deletedTag) {
                return Response::json(['error' => 'Tag not found'], 404);
            }

            if ($user->role === 'user') {
                return Response::json(['error' => 'Access denied'], 403);
            }

            $this->tagsModel->delete($id);
            return Response::json([
                'message' => 'Tag deleted successfully'
            ], 204);

        } catch (\Exception $error) {
            if ($error->getCode() === '23000') {
                return Response::json(['error' => 'Cannot delete: tag is in use'], 409);
            }
            return Response::json(['error' => 'Failed to remove tag: '.$error], 500);
        }
    }
}

<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\MessagesModel;
use TicketTracker\Models\TicketModel;
use TicketTracker\Helpers\Response;

class TicketController
{
    private TicketModel $ticketModel;
    private MessagesModel $messageModel;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->messageModel = new MessagesModel();
    }

    public function createTicket(\stdClass $user): never
    {
        try {
            $data = json_decode(file_get_contents('php://input') ?: '', true) ?: [];

            $ticketData = [
                'author_id' => $user->user,
                'title' => $data['title'],
                'description' => $data['description'],
                'tag_id' => $data['tag_id'],
            ];

            $ticketId = $this->ticketModel->create($ticketData);
            $ticket = $this->ticketModel->get($ticketId);

            Response::json([
                'data' => $ticket,
                'message' => 'Ticket created successfully'
            ], 201);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to create ticket: '.$error], 500);
        }
    }

    public function getTicket(\stdClass $user, string $id): never
    {
        try {
            $ticket = $this->ticketModel->get((int) $id);

            if (!$ticket) {
                Response::json(['error' => 'Ticket not found'], 404);
            }
            if ($user->role === 'user' && $ticket['author_id'] !== $user->user) {
                Response::json(['error' => 'Access denied'], 403);
            }

            Response::json([
                'data' => $ticket,
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get ticket: '.$error], 500);
        }
    }

    public function getTickets(\stdClass $user): never
    {
        try {
            if ($user->role === 'admin') {
                $tickets = $this->ticketModel->getAll();
            } else {
                $tickets = $this->ticketModel->getByUserId($user->user);
            }

            Response::json([
                'data' => $tickets,
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get tickets: '.$error], 500);
        }
    }

    public function removeTicket(\stdClass $user, string $id): never
    {
        try {
            $deletedTicket = $this->ticketModel->get((int) $id);
            if (!$deletedTicket) {
                Response::json(['error' => 'Ticket not found'], 404);
            } elseif ($user->role === 'user' && $deletedTicket['author_id'] !== $user->user) {
                Response::json(['error' => 'Access denied'], 403);
            } else {
                $this->ticketModel->delete((int) $id);
                Response::json([
                    'message' => 'Ticket deleted successfully'
                ], 204);
            }

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to remove ticket: '.$error], 500);
        }
    }

    public function updateStatus(\stdClass $user, string $id): never
    {
        try {
            if ($user->role !== 'admin') {
                Response::json(['error' => 'Admin access required'], 403);
            }

            $data = json_decode(file_get_contents('php://input') ?: '', true) ?: [];

            if (!$data['status_id']) {
                Response::json(['error' => 'Required status_id property'], 422);
            }

            $this->ticketModel->updateStatus((int) $id, (string) $data['status_id'], $user->user);

            $ticket = $this->ticketModel->get((int) $id);

            Response::json([
                'data' => $ticket,
                'message' => 'Status updated successfully'
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to update status: '.$error], 500);
        }
    }

    public function updateTag(\stdClass $user, string $id): never
    {
        try {
            if ($user->role !== 'admin') {
                Response::json(['error' => 'Admin access required'], 403);
            }

            $data = json_decode(file_get_contents('php://input') ?: '', true) ?: [];

            if (!$data['tag_id']) {
                Response::json(['error' => 'Required tag_id property'], 422);
            }

            $this->ticketModel->updateTag((int) $id, (string) $data['tag_id'], $user->user);

            $ticket = $this->ticketModel->get((int) $id);

            Response::json([
                'data' => $ticket,
                'message' => 'Tag updated successfully'
            ]);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to update tag: '.$error], 500);
        }
    }

    public function getMessage(\stdClass $user, string $id): never
    {
        try {
            $ticket = $this->ticketModel->get((int) $id);

            if (!$ticket) {
                Response::json(['error' => 'Ticket not found'], 404);
            }

            if ($user->role === 'user' && $user->user !== $ticket['author_id']) {
                Response::json(['error' => 'User access required'], 403);
            }

            $message = $this->messageModel->get($ticket['message_id']);

            Response::json([
                'success' => true,
                'data' => $message
            ], 201);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get message: '. $error], 500);
        }
    }

    public function addMessage(\stdClass $user, string $id): never
    {
        try {
            if ($user->role !== 'admin') {
                Response::json(['error' => 'Admin access required'], 403);
            }

            $ticket = $this->ticketModel->get((int) $id);

            if (!$ticket) {
                Response::json(['error' => 'Ticket not found'], 404);
            }

            $data = json_decode(file_get_contents('php://input') ?: '', true) ?: [];

            if (!$data['message']) {
                Response::json(['error' => 'Required message property'], 422);
            }

            $messageData = [
                'message' => $data['message'],
                'author_id' => $user->user,
                'ticket_id' => (int) $id,
            ];

            $message = $this->messageModel->create($messageData);

            $this->ticketModel->updateMessage($ticket['id'], $message, $user->user);

            Response::json([
                'success' => true,
                'data' => $message
            ], 200);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to get message: '. $error], 500);
        }
    }
}
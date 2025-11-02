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

    public function createTicket($user)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $ticketData = [
                'author_id' => $user->user,
                'title' => $data['title'],
                'description' => $data['description'],
                'tag_id' => $data['tag_id'],
            ];

            $ticketId = $this->ticketModel->create($ticketData);
            $ticket = $this->ticketModel->get($ticketId);

            return Response::json([
                'data' => $ticket,
                'message' => 'Ticket created successfully'
            ], 201);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to create ticket: '.$error], 500);
        }
    }

    public function getTicket($user, $id)
    {
        try {
            $ticket = $this->ticketModel->get($id);

            if (!$ticket) {
                return Response::json(['error' => 'Ticket not found'], 404);
            }
            if ($user->role === 'user' && $ticket['author_id'] !== $user->user) {
                return Response::json(['error' => 'Access denied'], 403);
            }

            return Response::json([
                'data' => $ticket,
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to get ticket: '.$error], 500);
        }
    }

    public function getTickets($user)
    {
        try {
            if ($user->role === 'admin') {
                $tickets = $this->ticketModel->getAll();
            } else {
                $tickets = $this->ticketModel->getByUserId($user->user);
            }

            return Response::json([
                'data' => $tickets,
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to get tickets: '.$error], 500);
        }
    }

    public function removeTicket($user, $id)
    {
        try {
            $deletedTicket = $this->ticketModel->get($id);
            if (!$deletedTicket) {
                return Response::json(['error' => 'Ticket not found'], 404);
            } elseif ($user->role === 'user' && $deletedTicket['author_id'] !== $user->user) {
                return Response::json(['error' => 'Access denied'], 403);
            } else {
                $this->ticketModel->delete($id);
                return Response::json([
                    'message' => 'Ticket deleted successfully'
                ], 204);
            }

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to remove ticket: '.$error], 500);
        }
    }

    public function updateStatus($user, $id)
    {
        try {
            if ($user->role !== 'admin') {
                return Response::json(['error' => 'Admin access required'], 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data['status_id']) {
                return Response::json(['error' => 'Required status_id property'], 422);
            }

            $this->ticketModel->updateStatus($id, $data['status_id'], $user->user);

            $ticket = $this->ticketModel->get($id);

            return Response::json([
                'data' => $ticket,
                'message' => 'Status updated successfully'
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to update status: '.$error], 500);
        }
    }

    public function updateTag($user, $id)
    {
        try {
            if ($user->role !== 'admin') {
                return Response::json(['error' => 'Admin access required'], 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data['tag_id']) {
                return Response::json(['error' => 'Required tag_id property'], 422);
            }

            $this->ticketModel->updateTag($id, $data['tag_id'], $user->user);

            $ticket = $this->ticketModel->get($id);

            return Response::json([
                'data' => $ticket,
                'message' => 'Tag updated successfully'
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to update tag: '.$error], 500);
        }
    }

    public function addMessage($user, $id)
    {
        try {
            if ($user->role !== 'admin') {
                return Response::json(['error' => 'Admin access required'], 403);
            }

            $ticket = $this->ticketModel->get($id);

            if (!$ticket) {
                return Response::json(['error' => 'Ticket not found'], 404);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data['message']) {
                return Response::json(['error' => 'Required message property'], 422);
            }

            $messageData = [
                'message' => $data['message'],
                'author_id' => $user->user,
                'ticket_id' => $id,
            ];

            $message = $this->messageModel->create($messageData);

            return Response::json([
                'success' => true,
                'data' => $message
            ], 201);

        } catch (\Exception $error) {
            Response::json(['error' => 'Failed to add message: '. $error], 500);
        }
    }
}
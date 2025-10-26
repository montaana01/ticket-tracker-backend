<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\TicketModel;
use TicketTracker\Helpers\Response;

class TicketController
{
    private TicketModel $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
    }

    public function createTicket($user)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $ticketData = [
                'author_id' => $user->sub,
                'title' => $data['title'],
                'description' => $data['description'],
                'tag_id' => $data['tag_id'],
            ];

            $ticketId = $this->ticketModel->create($ticketData);
            $ticket = $this->ticketModel->get($ticketId);

            return Response::json([
                'success' => true,
                'data' => $ticket
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
            if ($user->role === 'user' && $ticket['author_id'] !== $user->sub) {
                return Response::json(['error' => 'Access denied'], 403);
            }

            return Response::json([
                'success' => true,
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
                $tickets = $this->ticketModel->getByUserId($user->sub);
            }

            return Response::json([
                'success' => true,
                'data' => $tickets,
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to get tickets: '.$error], 500);
        }
    }

    public function updateStatus($user, $id)
    {
        try {
            if ($user->role !== 'admin') {
                return Response::json(['error' => 'Admin access required'], 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $this->ticketModel->updateStatus($id, $data['statusId'], $user->sub);

            $ticket = $this->ticketModel->get($id);

            return Response::json([
                'success' => true,
                'data' => $ticket
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

            $this->ticketModel->updateTag($id, $data['tag_id'], $user->sub);

            $ticket = $this->ticketModel->get($id);

            return Response::json([
                'success' => true,
                'data' => $ticket
            ]);

        } catch (\Exception $error) {
            return Response::json(['error' => 'Failed to update tag: '.$error], 500);
        }
    }
}
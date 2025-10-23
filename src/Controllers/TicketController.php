<?php

namespace TicketTracker\Controllers;

use TicketTracker\Models\TicketModel;

class TicketController
{
    private TicketModel $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
    }

    public function createTicket($data)
    {
        return $this->ticketModel;
    }
    public function getAllTickets()
    {
        return null;
    }
}
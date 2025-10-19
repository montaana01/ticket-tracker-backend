<?php
namespace TicketTracker\Models;

class TicketModel extends BasicModel
{
    public function __construct()
    {
        parent::__construct('tickets');
    }
}
<?php

namespace TicketTracker\Models;

use TicketTracker\Models\BasicModel;

class StatusesModel extends BasicModel
{
    public function __construct()
    {
        parent::__construct('statuses');
    }
}
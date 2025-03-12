<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketPrice extends Model
{
    protected $fillable = ['event_id', 'ticket_type', 'price', 'event_date_id'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

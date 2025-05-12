<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'status_id',
        'description',
    ];

    public $timestamps = true;

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }
}

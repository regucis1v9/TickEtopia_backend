<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'status_id',
        'description',
    ];

    public $timestamps = true;

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the user associated with the history record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status associated with the history record.
     */
    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }
}

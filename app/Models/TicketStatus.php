<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the ticket histories for this status.
     */
    public function ticketHistories()
    {
        return $this->hasMany(TicketHistory::class, 'status_id');
    }
}
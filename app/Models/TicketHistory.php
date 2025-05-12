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

    /**
     * Get the ticket associated with the history record.
     */
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
     * Get the status attribute.
     * This is a virtual attribute to replace the missing status relationship.
     */
    public function getStatusAttribute()
    {
        // Return a simple object with name property based on status_id
        // You can customize this based on your status IDs
        $statusName = 'Unknown';
        
        if ($this->status_id == 1) {
            $statusName = 'Purchased';
        } elseif ($this->status_id == 2) {
            $statusName = 'Used';
        } elseif ($this->status_id == 3) {
            $statusName = 'Cancelled';
        }
        
        return (object)[
            'id' => $this->status_id,
            'name' => $statusName
        ];
    }
}
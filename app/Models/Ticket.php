<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'event_id', 'ticket_number', 'status_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class); 
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $ticket->ticket_number = strtoupper(str_random(10)); 
        });
    }
}

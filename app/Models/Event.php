<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'is_public', 'image', 'organizer_id', 'location', 'venue_id'];

    public function eventDates()
    {
        return $this->hasMany(EventDate::class);
    }

    public function ticketPrices()
    {
        return $this->hasManyThrough(TicketPrice::class, EventDate::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class, 'organizer_id');
    }
    
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
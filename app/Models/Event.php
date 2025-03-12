<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'is_public', 'image', 'organizer_id'];

    public function eventDates()
    {
        return $this->hasMany(EventDate::class);
    }

    public function ticketPrices()
    {
        return $this->hasMany(TicketPrice::class);
    }
}

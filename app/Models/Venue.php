<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $fillable = ['name', 'address', 'contact_email', 'contact_phone', 'capacity', 'notes', 'image'];

    public function eventDates()
    {
        return $this->hasMany(EventDate::class);
    }
}
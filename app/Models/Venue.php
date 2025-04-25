<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'contact_email', 'contact_phone', 'capacity', 'notes', 'image'];

    public function eventDates()
    {
        return $this->hasMany(EventDate::class);
    }
}
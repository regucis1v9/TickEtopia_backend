<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_name',
        'organizer_registration_number',
        'organizer_email',
        'organizer_phone',
        'organizer_address',
        'image',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'address_book_id',
        'full_name',
        'username',
        'chat_id',
        'type',
        'blocked_at',
    ];
}

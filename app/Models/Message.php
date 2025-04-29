<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'address_book_id',
        'text',
        'sent_at',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}
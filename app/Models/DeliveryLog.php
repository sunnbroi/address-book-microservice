<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DeliveryLog extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'message_id',
        'recipient_id',
        'address_book_id',
        'status',
        'error_message',
    ];

    public $incrementing = false;
    protected $keyType = 'string'; 
}

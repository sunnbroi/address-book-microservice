<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;

class DeliveryLog extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'message_id',
        'address_book_id',
        'recipient_id',
        'status',
        'error',
        'status',
        'attempts',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }
    public function addressBook()
    {
        return $this->belongsTo(AddressBook::class);
    }

    public function prunable()
    {
        return static::where(function ($query) {
            $query->where('status', 'success')
                  ->where('updated_at', '<', now()->subDays(7))
                  ->orWhere(function ($q) {
                      $q->where('status', 'failed')
                        ->where('updated_at', '<', now()->subDays(30));
                    });
        });
    }
}

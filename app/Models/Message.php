<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    public $incrementing = false; // UUID
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'address_book_id',
        'type',
        'text',
        'sent_at',
        'link',
    ];

    public function addressBook()
    {
        return $this->belongsTo(AddressBook::class);
    }
    public function deliveryLogs()
    {
        return $this->hasMany(DeliveryLog::class);
    }
//     public function prunable()
// {
//     return static::where('sent_at', '<', now()->subMonths(6));
// }

}

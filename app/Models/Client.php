<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'client_key';
    public $incrementing = false;
    protected $keyType = 'string';
}

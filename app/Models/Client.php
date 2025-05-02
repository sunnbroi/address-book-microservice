<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Authenticatable
{
    use HasFactory, HasUuids, HasApiTokens;

    protected $primaryKey = 'client_key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['client_key', 'secret_key', 'host'];

}

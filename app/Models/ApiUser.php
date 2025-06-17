<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class ApiUser extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'id', ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }
}

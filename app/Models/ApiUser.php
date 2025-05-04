<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApiUser extends Authenticatable
{
    use HasApiTokens;

    public function client(): HasOne
{
    return $this->hasOne(Client::class);
}

}


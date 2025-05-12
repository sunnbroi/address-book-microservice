<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasUuids, HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'client_key';

    protected $fillable = [
        'client_key',
        'api_user_id',
        'name',
        'secret_key',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->client_key) {
                $model->client_key = Str::uuid();
            }
        });
    }

    public function apiUser(): BelongsTo
    {
        return $this->belongsTo(ApiUser::class);
    }

    public function addressBooks()
    {
        return $this->hasMany(AddressBook::class, 'client_key', 'client_key');
    }
}
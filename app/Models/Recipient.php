<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipient extends Model
{
    use SoftDeletes, HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'chat_id',
        'username',
        'first_name',
        'last_name',
        'type',
        'is_active',
        'blocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
        static::deleting(function (Recipient $model) {
            if (!$model->isForceDeleting()) {
                return;
            }
             if ($model->addressBooks()->exists()) {
                $model->addressBooks()->detach();
            }
        });
    }

    /**
     * Получатель принадлежит адресной книге.
     */
    public function addressBooks(): BelongsToMany
    {
        return $this->belongsToMany(AddressBook::class, 'address_books_recipients');
    }

    public function prunable(): Recipient
    {
        return static::where(function ($model) {
            $model->onlyTrashed()
                  ->whereDoesntHave('addressBooks')
                  ->where('deleted_at', '<=', now()->subDays(30));
        })->orWhere(function ($model) {
            $model->whereNotNull('blocked_at')
                  ->where('blocked_at', '<=', now()->subDays(30));
        });
}

        public function scopeActive($model)
    {
        return $model->where('is_active', true);
    }
}

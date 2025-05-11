<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'telegram_user_id',
        'username',
        'first_name',
        'last_name',
        'type',
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
        static::deleting(function ($addressBook) {
        // Сохраним IDs получателей, прежде чем отвязывать
        $recipientIds = $addressBook->recipients()->pluck('recipients.id')->toArray();

        // Отключаем связи (pivot)
        $addressBook->recipients()->detach();

        // Проверяем каждый recipient: связан ли он ещё с кем-то
        foreach ($recipientIds as $recipientId) {
            $stillAttached = \DB::table('address_books_recipients')
                ->where('recipient_id', $recipientId)
                ->exists();

            if (!$stillAttached) {
                // Если больше нигде не используется → мягко удаляем
                $recipient = \App\Models\Recipient::find($recipientId);
                if ($recipient && !$recipient->trashed()) {
                    $recipient->delete();
                }
            }
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

    public function prunable():Builder
    {
        return static::query()->where('deleted_at', '<=', now()->subDays(30));
    }

}

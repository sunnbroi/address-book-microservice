<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AddressBook extends Model
{
    // use SoftDeletes
    use HasUuids, SoftDeletes, Prunable, HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'client_key',
        'name',
        'invite_key',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (AddressBook $model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }    
            $model->invite_key = (string) Str::uuid();
        });

        static::deleting(function ($model) {
            if(!$model->isForceDeleting()) {
               return;
            }
    });}

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'address_books_recipients');
    }

    public function prunable(): Builder
    {
        return static::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30));
    }
    protected function pruning()
{
    // detach только перед окончательным удалением
    $this->recipients()->detach();
}
}

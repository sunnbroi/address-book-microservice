<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AddressBook extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'client_key',
        'name',
    ];
}

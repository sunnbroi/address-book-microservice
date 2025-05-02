<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Client;
class AddressBook extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'name', 'client_key'];
    public $incrementing = false;
    protected $keyType = 'string';
    
    public function client() {
        return $this->belongsTo(Client::class);
    }
}
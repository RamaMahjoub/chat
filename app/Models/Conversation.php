<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory, UUID;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'first_user_id',
        'second_user_id'
    ];

    public function messages(){
        return $this->hasMany(Message::class);
    }
}

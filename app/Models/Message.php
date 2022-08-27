<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, UUID;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'body','read','user_id','conversation_id'
    ];

    public function conversation(){
        return $this->belongsTo(Conversation::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['chat_session_id', 'role', 'content', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'content' => 'encrypted',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}

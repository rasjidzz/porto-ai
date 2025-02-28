<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prompt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['id', 'chat_session_id', 'user_question', 'ai_response'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id', 'chat_session_id');
    }
}

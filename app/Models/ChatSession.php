<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chat_sessions';
    protected $primaryKey = 'chat_session_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['chat_session_id'];

    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class, 'chat_session_id', 'chat_session_id');
    }
}

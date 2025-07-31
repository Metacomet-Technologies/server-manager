<?php

namespace Metacomet\ServerManager\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $session_id
 * @property string|int $user_id
 * @property string $command
 * @property string|null $output
 * @property int|null $exit_code
 * @property int|null $duration_ms
 */
class CommandHistory extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'exit_code' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function getTable()
    {
        return config('server-manager.tables.command_history', 'sm_command_history');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}

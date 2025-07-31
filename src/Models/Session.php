<?php

namespace Metacomet\ServerManager\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|int $user_id
 * @property string $server_id
 * @property string|null $name
 * @property bool $is_active
 * @property bool $is_shared
 * @property array|null $metadata
 * @property \Carbon\Carbon|null $last_activity_at
 * @property-read Server $server
 */
class Session extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('server-manager.tables.sessions', 'sm_sessions');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(SessionShare::class);
    }

    public function commandHistory(): HasMany
    {
        return $this->hasMany(CommandHistory::class);
    }

    public function isSharedWith($userId): bool
    {
        return $this->shares()
            ->where('shared_with_user_id', $userId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function canUserAccess($userId): bool
    {
        return (string) $this->user_id === (string) $userId || $this->isSharedWith($userId);
    }

    public function canUserExecute($userId): bool
    {
        if ((string) $this->user_id === (string) $userId) {
            return true;
        }

        return $this->shares()
            ->where('shared_with_user_id', $userId)
            ->where('permission', 'execute')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}

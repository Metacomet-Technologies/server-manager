<?php

namespace Metacomet\ServerManager\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $session_id
 * @property string|int $shared_with_user_id
 * @property string|int $shared_by_user_id
 * @property string $permission
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Session $session
 * @property-read mixed $sharedWithUser
 * @property-read mixed $sharedByUser
 */
class SessionShare extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    /** @var array<string, string> */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('server-manager.tables.session_shares', 'sm_session_shares');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function sharedWithUser(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'shared_with_user_id');
    }

    public function sharedByUser(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'shared_by_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}

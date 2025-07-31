<?php

namespace Metacomet\ServerManager\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|int $user_id
 * @property string $name
 * @property string $host
 * @property int $port
 * @property string $username
 * @property string $auth_type
 * @property string|null $password
 * @property string|null $private_key
 * @property string|null $key_passphrase
 * @property bool $is_local
 * @property array|null $metadata
 * @property-read array $connectionConfig
 */
class Server extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'is_local' => 'boolean',
        'password' => 'encrypted',
        'private_key' => 'encrypted',
        'key_passphrase' => 'encrypted',
    ];

    protected $hidden = [
        'password',
        'private_key',
        'key_passphrase',
    ];

    public function getTable()
    {
        return config('server-manager.tables.servers', 'sm_servers');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function activeSessions(): HasMany
    {
        return $this->sessions()->where('is_active', true);
    }

    protected function connectionConfig(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'host' => $this->host,
                'port' => $this->port,
                'username' => $this->username,
                'auth_type' => $this->auth_type,
                'password' => $this->password,
                'private_key' => $this->private_key,
                'key_passphrase' => $this->key_passphrase,
                'is_local' => $this->is_local,
            ]
        );
    }
}

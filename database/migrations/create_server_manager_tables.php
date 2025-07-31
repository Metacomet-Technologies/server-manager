<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function getUserModel(): string
    {
        return config('auth.providers.users.model', 'App\\Models\\User');
    }

    public function up()
    {
        $tablePrefix = config('server-manager.tables', [
            'servers' => 'sm_servers',
            'sessions' => 'sm_sessions',
            'session_shares' => 'sm_session_shares',
            'command_history' => 'sm_command_history',
        ]);

        $userModel = $this->getUserModel();

        // Servers table
        Schema::create($tablePrefix['servers'], function (Blueprint $table) use ($userModel) {
            $table->uuid('id')->primary();
            $table->foreignIdFor($userModel)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('host');
            $table->integer('port')->default(22);
            $table->string('username');
            $table->enum('auth_type', ['password', 'key', 'both'])->default('key');
            $table->text('password')->nullable();
            $table->text('private_key')->nullable();
            $table->string('key_passphrase')->nullable();
            $table->boolean('is_local')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });

        // Sessions table
        Schema::create($tablePrefix['sessions'], function (Blueprint $table) use ($userModel, $tablePrefix) {
            $table->uuid('id')->primary();
            $table->foreignIdFor($userModel)->constrained()->cascadeOnDelete();
            $table->foreignUuid('server_id')->constrained($tablePrefix['servers'])->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_shared')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('server_id');
        });

        // Session shares table
        Schema::create($tablePrefix['session_shares'], function (Blueprint $table) use ($userModel, $tablePrefix) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained($tablePrefix['sessions'])->cascadeOnDelete();
            $table->foreignIdFor($userModel, 'shared_with_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor($userModel, 'shared_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('permission', ['view', 'execute'])->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'shared_with_user_id']);
            $table->index('shared_with_user_id');
        });

        // Command history table
        Schema::create($tablePrefix['command_history'], function (Blueprint $table) use ($userModel, $tablePrefix) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained($tablePrefix['sessions'])->cascadeOnDelete();
            $table->foreignIdFor($userModel)->constrained()->cascadeOnDelete();
            $table->text('command');
            $table->longText('output')->nullable();
            $table->integer('exit_code')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        $tablePrefix = config('server-manager.tables', [
            'servers' => 'sm_servers',
            'sessions' => 'sm_sessions',
            'session_shares' => 'sm_session_shares',
            'command_history' => 'sm_command_history',
        ]);

        Schema::dropIfExists($tablePrefix['command_history']);
        Schema::dropIfExists($tablePrefix['session_shares']);
        Schema::dropIfExists($tablePrefix['sessions']);
        Schema::dropIfExists($tablePrefix['servers']);
    }
};

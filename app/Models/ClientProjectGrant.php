<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProjectGrant extends Model
{
    public const ROLES = ['Owner/Decision Maker', 'Technical Viewer', 'Finance Viewer', 'Read-only Viewer'];
    protected $table = 'client_project_grants';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'revoked_at' => 'datetime', 'approved_at' => 'datetime'];
    }
    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'client_user_id');
    }
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

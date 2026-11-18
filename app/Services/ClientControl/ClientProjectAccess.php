<?php

namespace App\Services\ClientControl;

use App\Models\ClientProjectGrant;
use App\Models\ClientUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/** Fail-closed access to explicitly approved, unexpired client project grants. */
final class ClientProjectAccess
{
    public function grants(ClientUser $client, ?int $projectId = null): Builder
    {
        $query = ClientProjectGrant::query()
            ->where('client_user_id', $client->getKey())
            ->where('status', 'Approved')
            ->whereNotNull('approved_at')
            ->whereNull('revoked_at')
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now()))
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', Carbon::now());

        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }
        return $query;
    }

    public function allowed(ClientUser $client, int $projectId, ?array $roles = null): bool
    {
        if ($client->status !== 'Active' || $client->deactivated_at !== null) {
            return false;
        }
        $query = $this->grants($client, $projectId);
        if ($roles !== null) {
            $query->whereIn('role', $roles);
        }
        return $query->exists();
    }

    public function assertAllowed(ClientUser $client, int $projectId, ?array $roles = null): void
    {
        abort_unless($this->allowed($client, $projectId, $roles), 403);
    }
}

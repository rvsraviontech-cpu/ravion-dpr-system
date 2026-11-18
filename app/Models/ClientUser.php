<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'client_users';
    protected $fillable = ['name', 'email', 'mobile', 'password', 'status', 'must_change_password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return ['password' => 'hashed', 'must_change_password' => 'boolean', 'email_verified_at' => 'datetime', 'last_login_at' => 'datetime', 'deactivated_at' => 'datetime'];
    }
    public function grants(): HasMany
    {
        return $this->hasMany(ClientProjectGrant::class, 'client_user_id');
    }
}

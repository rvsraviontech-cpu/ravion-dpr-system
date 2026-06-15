<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password','role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function role()
{
    return $this->belongsTo(Role::class);
}
public function projects()
{
    return $this->belongsToMany(Project::class);
}
public function dprs()
{
    return $this->hasMany(Dpr::class);
}

public function users()
{
    return $this->belongsToMany(User::class);
}

public function hasPermission($permissionName)
{
    if (!$this->role) {
        return false;
    }

    return $this->role
        ->permissions()
        ->where('name', $permissionName)
        ->where('is_active', true)
        ->exists();
}

}

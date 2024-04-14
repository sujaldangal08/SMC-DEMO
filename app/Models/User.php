<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Models\Role;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function deactivate(): void
    {
        $this->status = 'inactive';
        $this->save();
    }

    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->save();
    }

    public function incrementLoginAttempts(): void
    {
        $this->login_attempts++;
        $this->save();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $role): bool
    { //Function role      value role assigned to role
        return $this->role->role === $role;
    }

    public function schedule()
    {
        return $this->hasMany(PickupSchedule::class);
    }

    public function scopeHasRole($query, $role)
    {
        return $query->whereHas('role', function ($q) use ($role) {
            $q->where('role', $role);
        });
    }
}

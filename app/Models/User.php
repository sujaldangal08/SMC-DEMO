<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use CanResetPassword, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'phone_number',
        'city',
        'state',
        'country',
        'zip_code',
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'login_attempts',
        'status',
        'role_id',
        'tfa_secret',
        'is_tfa',
        'otp',
        'otp_expiry',
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

    /**
     * Check if the user is active
     *
     * @return bool
     */

    public function deactivate(): void
    {
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Reset the login attempts
     */

    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->save();
    }

    /*`
     * Increment the login attempts
     */

    public function incrementLoginAttempts(): void
    {
        $this->login_attempts++;
        $this->save();
    }

    /**
     * Get the role of the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user has a role
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role->role === $role;
    }

    /**
     * Check if the user is active
     *
     * @return bool
     */

    public function schedule()
    {
        return $this->hasMany(PickupSchedule::class);
    }

    /**
     * Check if the user is active
     *
     * @return bool
     */

    public function scopeHasRole($query, $role)
    {
        return $query->whereHas('role', function ($q) use ($role) {
            $q->where('role', $role);
        });
    }

    /**
     * Check if the user is active
     *
     * @return bool
     */

    public function delivery()
    {
        return $this->hasMany(Delivery::class);
    }
}

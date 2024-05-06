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

    public int $login_attempts;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'city',
        'state',
        'country',
        'role_id',
        'image',
        'login_attempts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expiry',
        'email_verified_at',
        'login_attempts',
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
            'login_attempts' => 'integer',
        ];
    }

    //Do not touch this code in any way or form as it is used for the login attempts feature
    //If you remove this code you get an error
    //Typed property App\\Models\\User::$login_attempts must not be accessed before initialization
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Initialize login_attempts if not set
        $this->login_attempts = $this->login_attempts ?? 0;
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
        $this->save();
    }

    // Do not touch this code in any way or form as it is used for the login attempts feature
    public function resetLoginAttempts(): void
    {
        $this->login_attempts = 0;
        $this->save();
    }

    public function incrementLoginAttempts(): void
    {
        $this->increment('login_attempts'); // Use Eloquent's increment method
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

    public function delivery()
    {
        return $this->hasMany(Delivery::class);
    }

    public function getImageAttribute($value)
    {
        return $value ? asset('storage/'.$value) : null;
    }

    public function routeNotificationForFirebase($notification)
    {
        return $this->device_token; // replace with the name of the device token column in your users table
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
// use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    // use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'host_id', //nullable (only if a user is a host)
        'fname',
        'lname',
        'email',
        'phone',
        'password',
        'otp',
        'otp_expires_at',
        'otp_verified_at',
        'email_verified_at',
        'facebook_id',
        'google_id',
        'profile_complete',
        'currency',
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

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

        // Always eager load:
        protected $with = ['details'];

        // Merge into user array output:
        public function toArray()
        {
            $array = parent::toArray();

            if ($this->relationLoaded('details') && $this->details) {
                $array = array_merge($array, $this->details->toArray());
                unset($array['details']);
            }

            return $array;
        }

    /**
     * Relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function details()
    {
        return $this->hasOne(UserDetail::class);
    }
}
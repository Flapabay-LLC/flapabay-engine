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
        'is_host', // boolean - indicates if user is a host
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
        'balance',
        'pending_earnings',
        'total_earnings',
        'total_withdrawn',
        'last_payout_at',
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
        'is_host',
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
            'is_host' => 'boolean',
            'balance' => 'decimal:2',
            'pending_earnings' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_withdrawn' => 'decimal:2',
            'last_payout_at' => 'datetime',
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
     * Determine if the user is a host.
     */
    public function isHost()
    {
        return (bool) (array_key_exists('is_host', $this->attributes) ? $this->attributes['is_host'] : false);
    }

    /**
     * Get the is_host attribute for JSON serialization.
     */
    public function getIsHostAttribute()
    {
        return (bool) (array_key_exists('is_host', $this->attributes) ? $this->attributes['is_host'] : false);
    }

    /**
     * Determine if the user is a guest.
     */
    public function isGuest()
    {
        return !$this->is_host;
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

    /**
     * Relationship with listing model (listings owned by this user).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function listings()
    {
        return $this->hasMany(listing::class, 'user_id');
    }

    /**
     * Relationship with withdrawals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Relationship with payment methods.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Get user's bookings as a guest.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get bookings for listings owned by this user (as host).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function hostBookings()
    {
        return $this->hasManyThrough(Booking::class, Listing::class, 'user_id', 'listing_id');
    }
}
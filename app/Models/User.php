<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Immersion\Models\Game;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // No 'password' => 'hashed' cast: that is Laravel 10+. On 9 the password
    // is hashed explicitly at every write site.
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Cases this user may play. Access is always read from here, never from an
     * order or a payment.
     */
    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    /**
     * Games this user owns as Game Master.
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /**
     * Does this user hold active access to the given case? Accepts a slug so
     * callers holding only the engine's identifier do not have to load the
     * catalog row first.
     */
    public function ownsCase(MysteryCase|string $case): bool
    {
        $query = $this->entitlements()->active();

        if ($case instanceof MysteryCase) {
            return $query->where('mystery_case_id', $case->id)->exists();
        }

        return $query->whereHas(
            'mysteryCase',
            fn (Builder $mysteryCase) => $mysteryCase->where('slug', $case)
        )->exists();
    }

    /**
     * @return \Illuminate\Support\Collection<int, MysteryCase>
     */
    public function library(): Collection
    {
        return MysteryCase::query()
            ->whereHas(
                'entitlements',
                fn (Builder $entitlement) => $entitlement->active()->where('user_id', $this->id)
            )
            ->ordered()
            ->get();
    }
}

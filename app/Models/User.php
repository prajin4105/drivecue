<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name', 'last_name', 'center_name', 'center_address', 'mobile',
        'password', 'role', 'mobile_verified',
        'whatsapp_message_template', 'status', 'profile_image',
        'whatsapp_language', 'auto_reminder_days',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'mobile_verified' => 'boolean',
        'password' => 'hashed',
        'auto_reminder_days' => 'array',
    ];

    // Accessor for name attribute (needed for Filament and packages)
    public function getNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: 'User';
    }

    // Accessor for email attribute (fallback for packages expecting email)
    public function getEmailAttribute(): string
    {
        return $this->mobile . '@drivecue.com';
    }

    // Helper methods for role checks
    public function isSuperAdmin(): bool
    {
        return (int) $this->role === 0;
    }

    public function isCenterOwner(): bool
    {
        return (int) $this->role === 1;
    }

    // Filament access authorization
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isSuperAdmin() && $this->status === 'active';
    }

    // Relationships
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function vehicleRecords(): HasMany
    {
        return $this->hasMany(VehicleRecord::class);
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }

    public function whatsappTopups(): HasMany
    {
        return $this->hasMany(WhatsappTopup::class);
    }
}

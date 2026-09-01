<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_GUIDE_EDITOR = 'guide_editor';
    public const ROLE_BUSINESS_OWNER = 'business_owner';
    public const ROLE_USER = 'user';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password_hash',
        'provider',
        'provider_id',
        'provider_email',
        'email_verified_at',
        'avatar',
        'role',
        'status',
        'location',
        'verified',
        'two_factor_auth',
        'subscription',
        'activity_level',
        'bio',
        'last_active_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'two_factor_auth' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Normalize arbitrary role representations into canonical values.
     */
    public static function normalizeRole(?string $role): string
    {
        if (!$role) {
            return self::ROLE_USER;
        }

        $cleaned = strtolower(trim($role));
        $cleaned = str_replace([' ', '/', '-'], '_', $cleaned);
        $cleaned = preg_replace('/_+/', '_', $cleaned);

        return match ($cleaned) {
            'super_admin', 'superadmin' => self::ROLE_SUPER_ADMIN,
            'admin', 'administrator' => self::ROLE_ADMIN,
            'guide_editor', 'guide', 'editor' => self::ROLE_GUIDE_EDITOR,
            'business_owner', 'business', 'owner' => self::ROLE_BUSINESS_OWNER,
            'user', 'tourist', 'member' => self::ROLE_USER,
            default => self::ROLE_USER,
        };
    }

    /**
     * Mutator to always persist normalized role string.
     */
    public function setRoleAttribute($value): void
    {
        $this->attributes['role'] = self::normalizeRole(is_string($value) ? $value : null);
    }

    /**
     * Override default Laravel password column name (password -> password_hash).
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function businesses()
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function reviewReplies()
    {
        return $this->hasMany(ReviewReply::class, 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function galleryMedia()
    {
        return $this->hasMany(GalleryMedia::class, 'uploaded_by_user_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'user_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_user_id');
    }

    public function deletionRequests()
    {
        return $this->hasMany(DeletionRequest::class, 'user_id');
    }

    public function achievements()
    {
        return $this->hasMany(UserAchievement::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function notificationSetting()
    {
        return $this->hasOne(UserNotificationSetting::class, 'user_id');
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class, 'user_id');
    }

    public function galleryComments()
    {
        return $this->hasMany(GalleryComment::class, 'user_id');
    }

    public function galleryLikes()
    {
        return $this->hasMany(GalleryLike::class, 'user_id');
    }

    public function isOnline(): bool
    {
        if (!$this->last_active_at) {
            return false;
        }

        return $this->last_active_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    public function isSuperAdmin(): bool
    {
        return self::normalizeRole($this->role) === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array(self::normalizeRole($this->role), [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN], true);
    }

    public function isGuideEditor(): bool
    {
        return self::normalizeRole($this->role) === self::ROLE_GUIDE_EDITOR;
    }

    public function isBusinessOwner(): bool
    {
        return self::normalizeRole($this->role) === self::ROLE_BUSINESS_OWNER;
    }

    public function isTourist(): bool
    {
        return self::normalizeRole($this->role) === self::ROLE_USER;
    }

    public function hasRole(string|array $roles): bool
    {
        $currentRole = self::normalizeRole($this->role);
        $roleList = is_array($roles) ? $roles : func_get_args();
        $normalizedList = array_map([self::class, 'normalizeRole'], $roleList);

        return in_array($currentRole, $normalizedList, true);
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array(self::normalizeRole($this->role), [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN], true);
    }

    public function canModerate(): bool
    {
        return in_array(self::normalizeRole($this->role), [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_GUIDE_EDITOR,
        ], true);
    }

    public function getOnlineStatusAttribute(): string
    {
        if ($this->isOnline()) {
            return 'Online Now';
        }

        if ($this->last_active_at) {
            return $this->last_active_at->diffForHumans();
        }

        return 'Offline';
    }

    public function getLastActiveHumanAttribute(): string
    {
        if ($this->isOnline()) {
            return 'Online Now';
        }

        if ($this->last_active_at) {
            return $this->last_active_at->diffForHumans();
        }

        return 'Never';
    }
}

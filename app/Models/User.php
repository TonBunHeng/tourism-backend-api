<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
     * Override default Laravel password column name (password -> password_hash).
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
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
        return strtolower(trim($this->role ?? '')) === 'super admin';
    }

    public function isAdmin(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        return in_array($role, ['super admin', 'admin'], true);
    }

    public function canModerate(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        return in_array($role, ['super admin', 'admin', 'guide / editor'], true);
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


<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    // Audience types: module_students, student_only, staff_only, all_users, everyone
    const AUDIENCE_MODULE_STUDENTS = 'module_students';
    const AUDIENCE_STUDENT_ONLY = 'student_only';
    const AUDIENCE_STAFF_ONLY = 'staff_only';
    const AUDIENCE_ALL_USERS = 'all_users';
    const AUDIENCE_EVERYONE = 'everyone';

    public static $audienceLabels = [
        'module_students' => 'Module Students',
        'student_only' => 'All Students',
        'staff_only' => 'Staff Only',
        'all_users' => 'All Users',
        'everyone' => 'Everyone (Public)',
    ];

    protected $fillable = [
        'title', 'description', 'category', 'audience', 'is_published', 'created_by', 'module_id'
    ];

    protected $casts = [
        'visible_to_roles' => 'array',
        'is_published' => 'boolean',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    public function latestVersion()
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(DocumentAcknowledgement::class);
    }

    // --- Scopes ---

    public function scopePublished($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForCategory($query, string $category): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', $category);
    }

    public function scopeForAudience($query, string $audience): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('audience', $audience);
    }

    public function scopeByCreator($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('created_by', $userId);
    }

    public function scopeForModule($query, int $moduleId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('module_id', $moduleId);
    }

    // --- Helpers ---

    public function isAcknowledgedBy(User $user): bool
    {
        return $this->acknowledgements()->where('user_id', $user->id)->exists();
    }
}

<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a chat channel.
 *
 * @package App\Models
 */
class ChatChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'channel_type',
        'channel_id',
        'participants',
    ];

    protected $casts = [
        'participants' => 'array',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'channel_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'channel_id');
    }

    public function scopeGeneral($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('channel_type', 'general');
    }

    public function scopeDepartment($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('channel_type', 'department');
    }

    public function scopeDirect($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('channel_type', 'direct');
    }

    public function scopeForUser($query, $user): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('channel_type', 'general')
              ->orWhere('channel_type', 'department')
              ->orWhereJsonContains('participants', (string) $user->id);
        });
    }

    /**
     * Get or create the general staff channel safely.
     * Prevents duplicate general channels due to NULL unique constraint behavior.
     */
    public static function getGeneralChannel(): self
    {
        $channel = self::where('channel_type', 'general')->first();

        if (!$channel) {
            $channel = self::create([
                'channel_type' => 'general',
                'channel_id' => null,
                'name' => 'All Staff',
            ]);
        }

        return $channel;
    }

    /**
     * Get or create a department channel safely.
     */
    public static function getDepartmentChannel(int $departmentId, string $name): self
    {
        $channel = self::where('channel_type', 'department')
            ->where('channel_id', $departmentId)
            ->first();

        if (!$channel) {
            $channel = self::create([
                'channel_type' => 'department',
                'channel_id' => $departmentId,
                'name' => $name,
            ]);
        }

        return $channel;
    }

    /**
     * Get or create a module channel safely.
     */
    public static function getModuleChannel(int $moduleId, string $name): self
    {
        $channel = self::where('channel_type', 'module')
            ->where('channel_id', $moduleId)
            ->first();

        if (!$channel) {
            $channel = self::create([
                'channel_type' => 'module',
                'channel_id' => $moduleId,
                'name' => $name,
            ]);
        }

        return $channel;
    }
}
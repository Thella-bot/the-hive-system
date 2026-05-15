<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Assignment extends Model
{
    protected $fillable = ['module_id', 'instructor_id', 'title', 'description', 'due_date', 'max_file_size', 'allowed_types'];
    protected $casts = ['due_date' => 'datetime'];
    public function module() { return $this->belongsTo(Module::class); }
    public function instructor() { return $this->belongsTo(User::class, 'instructor_id'); }
    public function submissions() { return $this->hasMany(Submission::class); }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('student')) {
            $moduleIds = $user->modules()->pluck('id');
            return $query->whereIn('module_id', $moduleIds);
        }

        if ($user->hasRole('instructor')) {
            return $query->where('instructor_id', $user->id);
        }

        return $query;
    }
}
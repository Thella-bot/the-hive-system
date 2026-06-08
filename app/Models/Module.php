<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = ['name', 'code', 'description', 'credits', 'department_id'];

    public function programmes(): BelongsToMany
    {
        return $this->belongsToMany(Programme::class, 'programme_module')
            ->withPivot('order_column')
            ->withTimestamps();
    }

    // Kept for backward compat — first programme linked via pivot
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
    public function department() { return $this->belongsTo(Department::class); }
    /**
     * The students that are enrolled in the module.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot('academic_year', 'semester')
                    ->withTimestamps();
    }
    public function instructors() {
    return $this->belongsToMany(User::class, 'module_instructor');
    }
    public function gradables()
    {
        return $this->hasMany(Gradable::class);
    }
}
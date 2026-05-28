<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['programme_id', 'name', 'code', 'description', 'credits', 'department_id'];
    public function programme() { return $this->belongsTo(Programme::class); }
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
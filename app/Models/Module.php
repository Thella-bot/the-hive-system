<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Module extends Model
{
    protected $fillable = ['programme_id', 'name', 'code', 'description', 'credits'];
    public function programme() { return $this->belongsTo(Programme::class); }
    public function assignments() { return $this->hasMany(Assignment::class); }
    public function gradeItems() { return $this->hasMany(GradeItem::class); }
    public function students() {
        return $this->belongsToMany(User::class, 'enrollments')
                    ->withPivot('academic_year', 'semester')
                    ->withTimestamps();
    }
    public function instructors() {
    return $this->belongsToMany(User::class, 'module_instructor');
    }
}
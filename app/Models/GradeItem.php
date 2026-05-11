<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GradeItem extends Model
{
    protected $fillable = ['module_id', 'name', 'type', 'max_marks', 'weight'];
    public function module() { return $this->belongsTo(Module::class); }
    public function studentGrades() { return $this->hasMany(StudentGrade::class); }
}
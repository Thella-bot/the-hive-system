<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentGrade extends Model
{
    protected $fillable = ['gradable_id', 'student_id', 'marks', 'comments'];
    public function gradable() { return $this->belongsTo(Gradable::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentGrade extends Model
{
    protected $fillable = ['grade_item_id', 'student_id', 'marks', 'comments'];
    public function gradeItem() { return $this->belongsTo(GradeItem::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
}
<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Submission extends Model
{
    protected $fillable = ['assignment_id', 'student_id', 'file_path', 'submitted_at', 'is_late', 'grade', 'feedback', 'graded_at', 'graded_by'];
    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
        'grade' => 'decimal:2'
    ];
    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function gradedBy() { return $this->belongsTo(User::class, 'graded_by'); }
}
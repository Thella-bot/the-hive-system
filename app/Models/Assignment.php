<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Assignment extends Model
{
    protected $fillable = ['module_id', 'instructor_id', 'title', 'description', 'due_date', 'max_file_size', 'allowed_types'];
    protected $casts = ['due_date' => 'datetime'];
    public function module() { return $this->belongsTo(Module::class); }
    public function instructor() { return $this->belongsTo(User::class, 'instructor_id'); }
    public function submissions() { return $this->hasMany(Submission::class); }
}
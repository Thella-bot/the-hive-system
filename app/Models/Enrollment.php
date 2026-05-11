<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Enrollment extends Model
{
    protected $fillable = ['user_id', 'module_id', 'academic_year', 'semester'];
    public function student() { return $this->belongsTo(User::class, 'user_id'); }
    public function module() { return $this->belongsTo(Module::class); }
}
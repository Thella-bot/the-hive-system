<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = ['user_id', 'month', 'file_path', 'uploaded_by'];

    public function user() { return $this->belongsTo(User::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
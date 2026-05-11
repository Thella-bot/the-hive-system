<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Programme extends Model
{
    protected $fillable = ['name', 'description', 'duration_years'];
    public function modules() { return $this->hasMany(Module::class); }
}
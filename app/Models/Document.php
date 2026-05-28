<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title', 'description', 'category', 'visible_to_roles', 'is_published', 'created_by', 'module_id'
    ];

    protected $casts = [
        'visible_to_roles' => 'array',
        'is_published' => 'boolean',
    ];

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    public function latestVersion()
    {
        return $this->hasOne(DocumentVersion::class)->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
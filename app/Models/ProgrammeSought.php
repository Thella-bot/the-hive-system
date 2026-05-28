<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgrammeSought extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'programme_id',
        'status',
        'notes',
    ];

    /**
     * Get the programme that the applicant is seeking.
     */
    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }
}

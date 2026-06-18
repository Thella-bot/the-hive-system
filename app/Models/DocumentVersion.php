<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id', 'version_number', 'file_path', 'uploaded_by'
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // --- Scopes ---

    public function scopeForDocument($query, int $documentId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('document_id', $documentId);
    }

    public function scopeByUploader($query, int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('uploaded_by', $userId);
    }
}
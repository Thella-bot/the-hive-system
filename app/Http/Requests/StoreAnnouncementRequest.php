<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'body'            => 'required|string',
            'body_html'       => 'nullable|string',
            'category'        => 'nullable|string|max:50',
            'target_roles'    => 'nullable|array',
            'target_modules'  => 'nullable|array',
            'target_modules.*'=> 'exists:modules,id',
            'is_pinned'       => 'nullable|boolean',
            'priority'        => 'nullable|in:normal,urgent,emergency',
            'expires_at'      => 'nullable|date',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('body_html')) {
            $this->merge([
                'body_html' => $this->sanitizeHtml($this->input('body_html')),
            ]);
        }
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if (!$html) {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><a><span><div><table><tr><td><th><thead><tbody>';
        $allowedAttributes = ['href', 'target', 'rel', 'class', 'style'];

        $html = strip_tags($html, $allowedTags);

        preg_match_all('/<[^>]+>/', $html, $tags);
        foreach ($tags[0] as $tag) {
            if (preg_match('/on\w+\s*=/i', $tag)) {
                $html = str_replace($tag, '', $html);
            }
        }

        $html = preg_replace('/javascript:/i', '', $html);
        $html = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);

        return $html;
    }
}
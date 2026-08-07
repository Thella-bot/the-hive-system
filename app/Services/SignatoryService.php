<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Config;

class SignatoryService
{
    public function forDocument(string $documentType): array
    {
        $config = Config::get("signatories.documents.{$documentType}");

        if (!$config) {
            return [
                'name' => Config::get('signatories.defaults.name'),
                'title' => 'Authorised Signatory',
            ];
        }

        $user = User::role($config['role'])->first();

        return [
            'name' => $user ? $user->name : Config::get('signatories.defaults.name'),
            'title' => $config['title'],
        ];
    }
}

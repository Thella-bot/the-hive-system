<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\Event;
use App\Models\Gradable;
use App\Models\Module;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = trim((string) $request->input('query', ''));
        $like = '%' . addcslashes($query, '%_\\') . '%';
        $user = $request->user();
        $roles = $user?->getRoleNames()->toArray() ?? [];
        $isAdmin = $user?->isAdmin() ?? false;
        $isStaff = $user?->isStaff() ?? false;

        $results = [
            'people' => [],
            'documents' => [],
            'announcements' => [],
            'events' => [],
            'assessments' => [],
            'modules' => [],
        ];

        if ($query !== '') {
            if ($isAdmin) {
                $results['people'] = User::query()
                    ->where(function ($builder) use ($like) {
                        $builder->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhereHas('profile', fn($q) => $q->where('student_number', 'like', $like));
                    })
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(fn (User $person) => [
                        'id' => $person->id,
                        'title' => $person->name,
                        'meta' => $person->profile?->student_number ?: $person->email,
                        'url' => route('hive.users.show', $person),
                    ]);
            }

            $documents = Document::query()
                ->where('is_published', true)
                ->where(function ($builder) use ($like) {
                    $builder->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('category', 'like', $like);
                });

            if (! $isAdmin && ! $isStaff) {
                $documents->where(function ($builder) use ($roles) {
                    $builder->whereNull('visible_to_roles');
                    foreach ($roles as $role) {
                        $builder->orWhereJsonContains('visible_to_roles', $role);
                    }
                });
            }

            $results['documents'] = $documents
                ->with('module:id,name,code')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Document $document) => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'meta' => trim(($document->category ?: 'Document') . ' ' . ($document->module ? 'in ' . $document->module->name : '')),
                    'url' => route('hive.documents.show', $document),
                ]);

            $results['announcements'] = Announcement::query()
                ->visibleTo($user)
                ->where(function ($builder) use ($like) {
                    $builder->where('title', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhere('category', 'like', $like);
                })
                ->latest()
                ->limit(8)
                ->get(['id', 'title', 'category', 'created_at'])
                ->map(fn (Announcement $announcement) => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'meta' => $announcement->category ?: 'Announcement',
                    'url' => route('hive.announcements.index'),
                ]);

            $results['events'] = Event::query()
                ->where(function ($builder) use ($like) {
                    $builder->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->orderBy('start')
                ->limit(8)
                ->get(['id', 'title', 'description', 'start'])
                ->map(fn (Event $event) => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'meta' => $event->start?->format('M j, Y g:i A') ?: 'Event',
                    'url' => route('hive.events.index'),
                ]);

            $results['assessments'] = Gradable::query()
                ->where(function ($builder) use ($like) {
                    $builder->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like)
                        ->orWhere('type', 'like', $like);
                })
                ->with('module:id,name,code')
                ->latest()
                ->limit(8)
                ->get()
                ->map(function (Gradable $gradable) {
                    $type = $gradable->type instanceof BackedEnum ? $gradable->type->value : (string) $gradable->type;

                    return [
                        'id' => $gradable->id,
                        'title' => $gradable->title,
                        'meta' => trim($type . ' ' . ($gradable->module ? 'for ' . $gradable->module->name : '')),
                        'url' => route('hive.gradables.show', $gradable),
                    ];
                });

            $results['modules'] = Module::query()
                ->where(function ($builder) use ($like) {
                    $builder->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('description', 'like', $like);
                })
                ->with('programme:id,name')
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->map(fn (Module $module) => [
                    'id' => $module->id,
                    'title' => $module->code ? "{$module->code} - {$module->name}" : $module->name,
                    'meta' => $module->programme?->name ?: 'Module',
                    'url' => route('hive.modules.index'),
                ]);
        }

        return Inertia::render('Hive/Search/Index', [
            'query' => $query,
            'results' => $results,
            'total' => collect($results)->sum(fn ($items) => count($items)),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly AuditService $audit,
    ) {}

    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()
            ->map(fn(Setting $setting) => [
                'key' => $setting->key,
                'value' => $setting->typedValue(),
                'type' => $setting->type,
                'group' => $setting->group,
                'label' => $setting->label,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ])
            ->values()
            ->all();

        $groups = collect($settings)
            ->pluck('group')
            ->unique()
            ->values()
            ->all();

        return Inertia::render('Hive/Settings/Index', [
            'settings' => $settings,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ]);

        // Only keys that exist as registered settings are written, so the form
        // cannot create arbitrary rows.
        $before = $this->settings->all();
        $updated = $this->settings->updateMany($validated['settings']);
        $after = $this->settings->all();

        $changed = array_keys(array_filter(
            array_keys($after),
            fn(string $key) => ($before[$key] ?? null) !== ($after[$key] ?? null)
        ));

        if ($changed) {
            $this->audit->log('settings.updated', newValues: [
                'keys' => $changed,
                'values' => array_intersect_key($after, array_flip($changed)),
            ]);
        }

        return back()->with('success', $updated > 0
            ? "{$updated} setting(s) saved."
            : 'No settings were changed.');
    }
}

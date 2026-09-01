<?php

namespace Database\Seeders;

use App\Models\Admin\Resource;
use App\Models\Admin\ResourceAction;
use App\Models\Admin\ResourcePermission;
use App\Support\ResourceSlug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Builds the resource/action/permission catalog mechanically from every
 * registered "user.*" named route, using the same canonicalization logic
 * the CheckResourcePermission middleware checks against — so the catalog
 * can never drift from the actual routes it's meant to gate.
 */
class ResourcePermissionSeeder extends Seeder
{
    /**
     * Friendlier display names for resource keys that don't read well
     * simply title-cased (e.g. "academic" -> "Academic Structure").
     */
    protected array $resourceLabels = [
        'academic' => 'Academic Structure',
        'team' => 'Team Management',
    ];

    /**
     * Friendlier display labels for actions beyond the standard CRUD verbs.
     */
    protected array $actionLabels = [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'import' => 'Import',
        'mark' => 'Mark',
        'generate' => 'Generate',
        'store' => 'Save',
    ];

    public function run(): void
    {
        $canonicalSlugs = collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter(fn ($name) => $name && str_starts_with($name, 'user.'))
            ->map(fn ($name) => substr($name, 5))
            ->reject(fn ($name) => in_array($name, ResourceSlug::$except, true))
            ->map(fn ($name) => ResourceSlug::canonicalize($name))
            ->unique()
            ->values();

        foreach ($canonicalSlugs as $slug) {
            $resourceKey = ResourceSlug::resourceKey($slug);
            $actionKey = ResourceSlug::actionKey($slug);

            $resource = Resource::firstOrCreate(
                ['slug' => $resourceKey],
                ['name' => $this->resourceLabels[$resourceKey] ?? Str::title(str_replace(['-', '_'], ' ', $resourceKey))]
            );

            $action = ResourceAction::firstOrCreate(
                ['resource_id' => $resource->id, 'action' => $actionKey],
                ['label' => $this->actionLabels[$actionKey] ?? Str::title(str_replace(['-', '_'], ' ', $actionKey))]
            );

            ResourcePermission::firstOrCreate(
                ['url_slug' => $slug],
                [
                    'resource_id' => $resource->id,
                    'resource_action_id' => $action->id,
                    'name' => $resource->name . ' — ' . $action->label,
                ]
            );
        }
    }
}

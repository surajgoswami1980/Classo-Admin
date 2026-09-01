<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Resource;
use App\Models\Admin\ResourceAction;
use App\Models\Admin\ResourcePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Super-admin management of the platform-wide resource/action/permission
 * catalog that policies are built from (see ManageTeamsController). The
 * catalog is normally auto-derived from routes by ResourcePermissionSeeder,
 * but display names and active status are editable here, and new custom
 * resources/actions can be added for anything not tied to a route.
 */
class AccessControlController extends Controller
{
    public function index()
    {
        $resources = Resource::with('actions.permissions')->orderBy('name')->get();

        return view('admin.access-control.index', compact('resources'));
    }

    public function storeResource(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|alpha_dash|unique:resources,slug',
        ]);

        Resource::create($validated + ['status' => true]);

        return back()->with('success', 'Resource created.');
    }

    public function updateResource(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $resource->update($validated);

        return back()->with('success', 'Resource updated.');
    }

    public function toggleResourceStatus(Resource $resource)
    {
        $resource->update(['status' => !$resource->status]);

        return back()->with('success', 'Resource status updated.');
    }

    public function storeAction(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'action' => 'required|string|max:50|alpha_dash',
            'label' => 'required|string|max:100',
        ]);

        $action = ResourceAction::create([
            'resource_id' => $resource->id,
            'action' => $validated['action'],
            'label' => $validated['label'],
            'status' => true,
        ]);

        ResourcePermission::create([
            'resource_id' => $resource->id,
            'resource_action_id' => $action->id,
            'name' => "{$resource->name} — {$action->label}",
            'url_slug' => "{$resource->slug}.{$action->action}",
            'status' => true,
        ]);

        return back()->with('success', 'Action added.');
    }

    public function updateAction(Request $request, ResourceAction $action)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
        ]);

        $action->update($validated);

        return back()->with('success', 'Action updated.');
    }

    public function toggleActionStatus(ResourceAction $action)
    {
        $newStatus = !$action->status;
        $action->update(['status' => $newStatus]);
        $action->permissions()->update(['status' => $newStatus]);

        return back()->with('success', 'Action status updated.');
    }
}

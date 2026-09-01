@extends('layouts.app')

@section('title', 'Team Management')
@section('page-title', 'Team Management')

@section('content')
<div x-data="{ tab: 'users' }" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Team Management</h2>
            <p class="text-sm text-gray-500">Give staff limited, permission-based access to this admin panel.</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-6 -mb-px">
            <button @click="tab = 'users'" :class="tab === 'users' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="py-3 px-1 border-b-2 text-sm font-medium transition">Team Members</button>
            <button @click="tab = 'roles'" :class="tab === 'roles' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="py-3 px-1 border-b-2 text-sm font-medium transition">Roles</button>
            <button @click="tab = 'policies'" :class="tab === 'policies' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="py-3 px-1 border-b-2 text-sm font-medium transition">Policies</button>
        </nav>
    </div>

    <div x-show="tab === 'users'">
        @include('admin.team.partials.users-tab')
    </div>
    <div x-show="tab === 'roles'" x-cloak>
        @include('admin.team.partials.roles-tab')
    </div>
    <div x-show="tab === 'policies'" x-cloak>
        @include('admin.team.partials.policies-tab')
    </div>
</div>
@endsection

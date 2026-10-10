@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Send Notifications')

@section('content')
<div x-data="notifier()" class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Total Sent</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Delivered</p><p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['sent'] }}</p></div>
        <div class="bg-white rounded-xl border p-4"><p class="text-xs text-gray-500">Active Devices</p><p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['active_tokens'] }}</p></div>
    </div>

    {{-- Compose --}}
    <div class="bg-white rounded-xl border p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Compose Notification</h3>
        <form method="POST" action="{{ panel_route('notifications.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Title *</label>
                <input type="text" name="title" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="e.g. Holiday Announcement">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Message *</label>
                <textarea name="body" required rows="3" maxlength="2000" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Write your message..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Delivery</label>
                    <select name="channel" x-model="channel" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="all">Push + In-App</option>
                        <option value="in_app">In-App only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Audience</label>
                    <select name="target_type" x-model="targetType" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="all">Everyone in school</option>
                        <option value="role">By role</option>
                        <option value="class">A class</option>
                        <option value="section">A section</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div x-show="targetType==='role'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="target_role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="student">Students</option>
                        <option value="teacher">Teachers</option>
                        <option value="parent">Parents</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div x-show="targetType==='class' || targetType==='section'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                    <select name="target_class_id" x-model="classId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select class</option>
                        @foreach($classes as $cid => $cname)<option value="{{ $cid }}">{{ $cname }}</option>@endforeach
                    </select>
                </div>
                <div x-show="targetType==='section'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Section</label>
                    <select name="target_section_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Select section</option>
                        @foreach($sections as $s)<option value="{{ $s->id }}" x-show="String(classId)===String({{ $s->class_id }})">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Send Notification</button>
            </div>
        </form>
    </div>

    {{-- History --}}
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50"><h3 class="text-sm font-semibold text-gray-800">Recent Notifications</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Audience</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Recipients</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Sent</th>
            </tr></thead>
            <tbody class="divide-y">
                @forelse($notifications as $n)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $n->title }}</p>
                        <p class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($n->body, 60) }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs capitalize">
                        {{ $n->target_type }}{{ $n->target_role ? ': '.$n->target_role : '' }}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-700">{{ $n->recipients }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $sb = ['queued'=>'text-amber-700 bg-amber-50','processing'=>'text-blue-700 bg-blue-50','sent'=>'text-green-700 bg-green-50','failed'=>'text-red-700 bg-red-50'][$n->status] ?? 'text-gray-700 bg-gray-100'; @endphp
                        <span class="text-xs font-medium {{ $sb }} px-2 py-0.5 rounded-full">{{ ucfirst($n->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $n->sent_at ? \Illuminate\Support\Carbon::parse($n->sent_at)->diffForHumans() : '—' }}</td>
                </tr>
                @empty<tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No notifications sent yet.</td></tr>@endforelse
            </tbody>
        </table>
        @if($notifications->hasPages())<div class="px-4 py-3 border-t">{{ $notifications->links() }}</div>@endif
    </div>
</div>

@push('scripts')
<script>
    function notifier() {
        return { channel: 'all', targetType: 'all', classId: '' };
    }
</script>
@endpush
@endsection

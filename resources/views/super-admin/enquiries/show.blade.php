@extends('layouts.app')

@section('title', 'Enquiry')
@section('page-title', 'Enquiry Detail')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <a href="{{ route('admin.enquiries.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        Back to Enquiries
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Lead details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden animate-fade-up">
                <div class="bg-gradient-to-r from-blue-600 to-cyan-500 px-6 py-5 text-white">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-white/20 text-xl font-bold">
                            {{ strtoupper(substr($enquiry->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">{{ $enquiry->name }}</h2>
                            <p class="text-white/80 text-sm">{{ $enquiry->organization ?: 'Individual enquiry' }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @php
                        $fields = [
                            ['Email', $enquiry->email, 'mailto:' . $enquiry->email],
                            ['Phone', $enquiry->phone ?: '—', $enquiry->phone ? 'tel:' . $enquiry->phone : null],
                            ['City', $enquiry->city ?: '—', null],
                            ['Approx. Students', $enquiry->student_count ? number_format($enquiry->student_count) : '—', null],
                            ['Source', $enquiry->source, null],
                            ['Received', $enquiry->created_at->format('d M Y, h:i A'), null],
                        ];
                    @endphp
                    @foreach($fields as [$label, $value, $href])
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                            @if($href)
                                <a href="{{ $href }}" class="mt-0.5 block text-sm font-medium text-primary-600 hover:underline">{{ $value }}</a>
                            @else
                                <p class="mt-0.5 text-sm font-medium text-gray-900">{{ $value }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($enquiry->subject || $enquiry->message)
                    <div class="px-6 pb-6">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Message</p>
                        @if($enquiry->subject)<p class="text-sm font-semibold text-gray-900">{{ $enquiry->subject }}</p>@endif
                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $enquiry->message ?: '—' }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Workflow panel --}}
        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 animate-fade-up">
                @csrf
                @method('PUT')
                <h3 class="text-sm font-bold text-gray-900 mb-4">Manage Lead</h3>

                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Status</label>
                <select name="status" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none mb-4">
                    @foreach(\App\Models\Enquiry::STATUSES as $s)
                        <option value="{{ $s }}" @selected($enquiry->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>

                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Internal Notes</label>
                <textarea name="admin_notes" rows="5" placeholder="Call summary, next steps…"
                          class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 outline-none mb-4">{{ old('admin_notes', $enquiry->admin_notes) }}</textarea>

                <button class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                    Save Changes
                </button>
            </form>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.payment-links.index', ['enquiry' => $enquiry->id]) }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                        Send onboarding payment link
                    </a>
                    <a href="mailto:{{ $enquiry->email }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        Send Email
                    </a>
                    @if($enquiry->phone)
                        <a href="tel:{{ $enquiry->phone }}" class="flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            Call {{ $enquiry->phone }}
                        </a>
                    @endif
                    <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" onsubmit="return confirm('Delete this enquiry permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="flex items-center gap-2 w-full px-3 py-2.5 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                            Delete Enquiry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

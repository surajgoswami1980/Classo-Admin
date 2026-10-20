@extends('layouts.app')

@section('title', 'Fee Categories')
@section('page-title', 'Fee Categories')

@section('content')
<div class="space-y-6" x-data="{ showCat:false, showPlan:false, showGen:false, genType:'monthly' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Fee Categories & Plans</h2>
            <p class="text-sm text-gray-500">Define categories (Tuition, Transport, Exam…), set per-class amounts, then generate invoices.</p>
        </div>
        <div class="flex gap-2">
            <button @click="showCat=true" class="btn btn-primary">+ Category</button>
            <button @click="showPlan=true" class="btn btn-success">+ Class Plan</button>
            <button @click="showGen=true" class="btn btn-secondary">Generate Invoices</button>
        </div>
    </div>

    @if(session('success'))<div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif

    <!-- Categories -->
    <div class="mc-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-900">Categories</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Name</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Type</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Description</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-primary-50/40 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $cat->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full
                                    @class(['bg-blue-100 text-blue-700' => $cat->type==='monthly','bg-primary-100 text-primary-700' => $cat->type==='exam','bg-gray-100 text-gray-700' => $cat->type==='one_time'])">
                                    {{ str_replace('_',' ', ucfirst($cat->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $cat->description ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ panel_route('fees.categories.delete', $cat->id) }}" data-confirm="Its plans will also be removed." data-confirm-title="Delete this category?" data-confirm-ok="Delete" data-confirm-tone="danger" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-700 text-xs font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Class plans -->
    <div class="mc-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-900">Class Fee Plans</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Category</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Class</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Amount</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Due Day</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-primary-50/40 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $plan->category_name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $plan->class_name }}</td>
                            <td class="px-6 py-4 text-gray-900 font-semibold">₹{{ number_format($plan->amount) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $plan->category_type==='monthly' ? $plan->due_day : '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ panel_route('fees.plans.delete', $plan->id) }}" data-confirm="This action can't be undone." data-confirm-title="Remove this plan?" data-confirm-ok="Remove" data-confirm-tone="danger" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-700 text-xs font-medium">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">No class plans yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Category modal -->
    <div x-show="showCat" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]">
        <div class="bg-white rounded-xl w-full max-w-md p-6" @click.outside="showCat=false">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">New Fee Category</h3>
            <form method="POST" action="{{ panel_route('fees.categories.store') }}" class="space-y-3">
                @csrf
                <div><label class="form-label">Name</label>
                    <input name="name" required class="form-input" placeholder="e.g. Tuition Fee"></div>
                <div><label class="form-label">Type</label>
                    <select name="type" class="form-input">
                        <option value="monthly">Monthly (recurring each month)</option>
                        <option value="one_time">One-time</option>
                        <option value="exam">Exam fee</option>
                    </select></div>
                <div><label class="form-label">Description</label>
                    <input name="description" class="form-input"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCat=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Plan modal -->
    <div x-show="showPlan" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]">
        <div class="bg-white rounded-xl w-full max-w-md p-6" @click.outside="showPlan=false">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Set Class Fee Plan</h3>
            <form method="POST" action="{{ panel_route('fees.plans.store') }}" class="space-y-3">
                @csrf
                <div><label class="form-label">Category</label>
                    <select name="fee_category_id" required class="form-input">
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }} ({{ str_replace('_',' ',$cat->type) }})</option>@endforeach
                    </select></div>
                <div><label class="form-label">Class</label>
                    <select name="class_id" required class="form-input">
                        @foreach($classes as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                    </select></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">Amount (₹)</label>
                        <input name="amount" type="number" min="0" step="0.01" required class="form-input"></div>
                    <div><label class="form-label">Due Day (monthly)</label>
                        <input name="due_day" type="number" min="1" max="28" value="10" class="form-input"></div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showPlan=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Generate modal -->
    <div x-show="showGen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-navy-950/50 backdrop-blur-[2px]">
        <div class="bg-white rounded-xl w-full max-w-md p-6" @click.outside="showGen=false">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Invoices</h3>
            <form method="POST" action="{{ panel_route('fees.categories.generate') }}" class="space-y-3">
                @csrf
                <div><label class="form-label">Category</label>
                    <select name="fee_category_id" required class="form-input"
                            @change="genType = $event.target.selectedOptions[0].dataset.type">
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }} ({{ str_replace('_',' ',$cat->type) }})</option>@endforeach
                    </select></div>

                <template x-if="genType==='monthly'">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="form-label">Month</label>
                            <select name="month" class="form-input">
                                @foreach(range(1,12) as $m)<option value="{{ $m }}" {{ (int)date('n')===$m?'selected':'' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endforeach
                            </select></div>
                        <div><label class="form-label">Year</label>
                            <input name="year" type="number" value="{{ date('Y') }}" class="form-input"></div>
                    </div>
                </template>

                <template x-if="genType!=='monthly'">
                    <div class="space-y-3">
                        <div><label class="form-label">Title (optional)</label>
                            <input name="title" class="form-input" placeholder="e.g. Term 1 Exam Fee"></div>
                        <div><label class="form-label">Due Date</label>
                            <input name="due_date" type="date" class="form-input"></div>
                        <div><label class="form-label">Exam ID (for exam fee, optional)</label>
                            <input name="exam_id" type="number" class="form-input"></div>
                    </div>
                </template>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showGen=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

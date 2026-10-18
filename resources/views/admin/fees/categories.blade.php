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
            <button @click="showCat=true" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">+ Category</button>
            <button @click="showPlan=true" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">+ Class Plan</button>
            <button @click="showGen=true" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Generate Invoices</button>
        </div>
    </div>

    @if(session('success'))<div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif

    <!-- Categories -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-900">Categories</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $cat->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full
                                    @class(['bg-blue-100 text-blue-700' => $cat->type==='monthly','bg-purple-100 text-purple-700' => $cat->type==='exam','bg-gray-100 text-gray-700' => $cat->type==='one_time'])">
                                    {{ str_replace('_',' ', ucfirst($cat->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $cat->description ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ panel_route('fees.categories.delete', $cat->id) }}" onsubmit="return confirm('Delete this category? Its plans will also be removed.')" class="inline">
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
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-900">Class Fee Plans</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Due Day</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $plan->category_name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $plan->class_name }}</td>
                            <td class="px-6 py-4 text-gray-900 font-semibold">₹{{ number_format($plan->amount) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $plan->category_type==='monthly' ? $plan->due_day : '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ panel_route('fees.plans.delete', $plan->id) }}" onsubmit="return confirm('Remove this plan?')" class="inline">
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
    <div x-show="showCat" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-xl w-full max-w-md p-6" @click.outside="showCat=false">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">New Fee Category</h3>
            <form method="POST" action="{{ panel_route('fees.categories.store') }}" class="space-y-3">
                @csrf
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                    <input name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Tuition Fee"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="monthly">Monthly (recurring each month)</option>
                        <option value="one_time">One-time</option>
                        <option value="exam">Exam fee</option>
                    </select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <input name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCat=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Plan modal -->
    <div x-show="showPlan" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-xl w-full max-w-md p-6" @click.outside="showPlan=false">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Set Class Fee Plan</h3>
            <form method="POST" action="{{ panel_route('fees.plans.store') }}" class="space-y-3">
                @csrf
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                    <select name="fee_category_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }} ({{ str_replace('_',' ',$cat->type) }})</option>@endforeach
                    </select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                    <select name="class_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach($classes as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                    </select></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Amount (₹)</label>
                        <input name="amount" type="number" min="0" step="0.01" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Due Day (monthly)</label>
                        <input name="due_day" type="number" min="1" max="28" value="10" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showPlan=false" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancel</button>
                    <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Generate modal -->
    <div x-show="showGen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-xl w-full max-w-md p-6" @click.outside="showGen=false">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Invoices</h3>
            <form method="POST" action="{{ panel_route('fees.categories.generate') }}" class="space-y-3">
                @csrf
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                    <select name="fee_category_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            @change="genType = $event.target.selectedOptions[0].dataset.type">
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" data-type="{{ $cat->type }}">{{ $cat->name }} ({{ str_replace('_',' ',$cat->type) }})</option>@endforeach
                    </select></div>

                <template x-if="genType==='monthly'">
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Month</label>
                            <select name="month" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                @foreach(range(1,12) as $m)<option value="{{ $m }}" {{ (int)date('n')===$m?'selected':'' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endforeach
                            </select></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                            <input name="year" type="number" value="{{ date('Y') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                    </div>
                </template>

                <template x-if="genType!=='monthly'">
                    <div class="space-y-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Title (optional)</label>
                            <input name="title" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Term 1 Exam Fee"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Due Date</label>
                            <input name="due_date" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Exam ID (for exam fee, optional)</label>
                            <input name="exam_id" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></div>
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

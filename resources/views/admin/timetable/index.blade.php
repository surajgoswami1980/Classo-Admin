@extends('layouts.app')

@section('title', 'Timetable Management')
@section('page-title', 'Timetable Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Timetable Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
            <i class="fas fa-plus me-1"></i> Add Period
        </button>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select" id="filterClass" onchange="this.form.submit()">
                        <option value="">Select Class</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['class_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select" id="filterSection">
                        <option value="">Select Section</option>
                        @foreach($sections as $sec)
                            @if(($filters['class_id'] ?? '') == $sec->class_id)
                                <option value="{{ $sec->id }}" {{ ($filters['section_id'] ?? '') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary">Load Timetable</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Timetable Grid --}}
    @if($timetable->count() > 0)
    <div class="card">
        <div class="card-body table-responsive">
            @php
                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                $grouped = $timetable->groupBy('day_of_week');
            @endphp
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Day</th>
                        <th>Period</th>
                        <th>Time</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                        @if($grouped->has($day))
                            @foreach($grouped[$day]->sortBy('period_number') as $i => $period)
                            <tr>
                                @if($i === 0)
                                <td rowspan="{{ $grouped[$day]->count() }}" class="fw-semibold text-capitalize align-middle">{{ $day }}</td>
                                @endif
                                <td>P{{ $period->period_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}</td>
                                <td>{{ $period->subject_name }}</td>
                                <td>{{ $period->teacher_name }}</td>
                                <td>
                                    <form method="POST" action="{{ route(request()->routeIs('admin.*') ? 'admin.timetable.destroy' : 'user.timetable.destroy', $period->id) }}" onsubmit="return confirm('Remove this period?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif(!empty($filters['class_id']) && !empty($filters['section_id']))
    <div class="alert alert-info">No timetable periods found for this class/section. Add periods using the button above.</div>
    @endif
</div>

{{-- Add Period Modal --}}
<div class="modal fade" id="addPeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route(request()->routeIs('admin.*') ? 'admin.timetable.store' : 'user.timetable.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Timetable Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select</option>
                                @foreach($classes as $id => $name)
                                    <option value="{{ $id }}" {{ ($filters['class_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select name="section_id" class="form-select" required>
                                <option value="">Select</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec->id }}" data-class="{{ $sec->class_id }}" {{ ($filters['section_id'] ?? '') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select Teacher</option>
                                @foreach($teachers as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Day <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-select" required>
                                @foreach(['monday','tuesday','wednesday','thursday','friday','saturday'] as $d)
                                    <option value="{{ $d }}">{{ ucfirst($d) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Period # <span class="text-danger">*</span></label>
                            <input type="number" name="period_number" class="form-control" min="1" max="12" required placeholder="1-12">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                            <select name="academic_session_id" class="form-select" required>
                                @foreach($sessions as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Period</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

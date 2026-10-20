@props(['status' => null, 'label' => null])

{{-- Maps common status strings to the design-system badge tones. --}}
@php
    $s = strtolower((string) $status);
    $tone = match (true) {
        in_array($s, ['active', 'paid', 'success', 'present', 'published', 'approved', 'completed', 'processed', 'issued', 'delivered', 'collected', 'sent', 'resolved', 'converted']) => 'success',
        in_array($s, ['pending', 'partial', 'late', 'trial', 'draft', 'scheduled', 'queued', 'in_progress', 'contacted', 'half_day', 'purchased', 'manual']) => 'warning',
        in_array($s, ['inactive', 'failed', 'overdue', 'absent', 'expired', 'cancelled', 'rejected', 'suspended', 'lost', 'closed']) => 'danger',
        in_array($s, ['new', 'info', 'leave', 'upcoming', 'returned']) => 'info',
        default => 'neutral',
    };
@endphp
<span {{ $attributes->merge(['class' => 'badge badge-' . $tone]) }}>{{ $label ?? ucwords(str_replace(['_', '-'], ' ', $s ?: '—')) }}</span>

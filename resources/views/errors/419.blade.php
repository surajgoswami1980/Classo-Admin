@extends('errors.layout')
@section('code', '419')
@section('code-gradient', 'from-amber-500 to-orange-600')
@section('title', 'Page expired')
@section('message', 'Your session timed out for security. Please refresh the page and try again.')
@section('extra')
<div class="mt-4"><button onclick="location.reload()" class="text-sm font-medium text-primary-600 hover:underline">Refresh page →</button></div>
@endsection

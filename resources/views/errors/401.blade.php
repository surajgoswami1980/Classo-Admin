@extends('errors.layout')
@section('code', '401')
@section('code-gradient', 'from-amber-500 to-orange-600')
@section('title', 'Not authenticated')
@section('message', 'Your session has expired or you are not signed in. Please log in again to continue.')
@section('extra')
<div class="mt-4"><a href="{{ route('login') }}" class="text-sm font-medium text-primary-600 hover:underline">Go to login →</a></div>
@endsection

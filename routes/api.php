<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// Simple API for AJAX calls within admin panel

Route::get('/students', function (Request $request) {
    $classId = $request->get('class_id');
    $sectionId = $request->get('section_id');

    if (!$classId || !$sectionId) {
        return response()->json(['data' => []]);
    }

    $students = DB::table('students')
        ->join('users', 'students.user_id', '=', 'users.id')
        ->where('students.class_id', $classId)
        ->where('students.section_id', $sectionId)
        ->where('students.status', 'active')
        ->select(['students.id', 'students.roll_number', 'users.name'])
        ->orderBy('students.roll_number')
        ->get();

    return response()->json(['data' => $students]);
});

Route::get('/sections', function (Request $request) {
    $classId = $request->get('class_id');
    if (!$classId) return response()->json(['data' => []]);

    $sections = DB::table('sections')
        ->where('class_id', $classId)
        ->select(['id', 'name'])
        ->get();

    return response()->json(['data' => $sections]);
});

<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::query()
            ->with(['student'])
            ->when($request->filled('student_name'), function($q) use ($request) {
                $q->whereHas('student', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->student_name . '%');
                });
            })
            ->when($request->filled('subject'), function($q) use ($request) {
                $q->where('subject', $request->subject);
            })
            ->orderBy('created_at', $request->input('sort', 'desc'));

        $grades = $query->paginate(10)->withQueryString();
        $subjects = Subject::All();
 
        return view('students.grades', compact('grades', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject' => 'required',
            'grade' => 'required|numeric|min:1|max:10'
        ]);

        // First find or create the subject
        $subject = Subject::firstOrCreate(
            ['id' => $request->subject],
            ['name' => $request->subject_name]
        );

        // Create the grade with the subject
        $grade = Grade::create([
            'student_id' => $request->student_id,
            'subject' => $subject->id,
            'grade' => $request->grade
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grade added successfully',
            'data' => $grade
        ]);
    }

    public function update(Request $request, Grade $grade)
    {
        try {
            $validated = $request->validate([
                'grade' => 'required|integer|between:1,10'
            ]);

            $grade->update($validated);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => 'Grade updated successfully'])
                : back()->with('success', 'Grade updated successfully');
        } catch (\Exception $e) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $e->getMessage()], 422)
                : back()->withErrors(['error' => 'Failed to update grade']);
        }
    }

    public function studentGrades(Request $request)
    {
        $query = Grade::where('student_id', auth()->user()->student->id)
            ->with('subject')
            ->when($request->has('subject'), function($q) use ($request) {
                $q->where('subject', $request->subject);
            })
            ->orderBy('created_at', $request->sort ?? 'desc');

        return view('students.grades', [
            'grades' => $query->paginate(10)->withQueryString(),
            'subjects' => Subject::pluck('name', 'id')
        ]);
    }
}

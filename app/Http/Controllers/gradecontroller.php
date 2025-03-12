<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    public function index(Request $request)
{
    $semesters = DB::table('tblsemester')->where('deleted', '0')->get();
    $selectedSemester = $request->input('sem_code');

    $user = auth()->user();
    $school_code = $user->school_code;
    
    // Initialize $grades and GPA variables
    $grades = collect();
    $totalGradePoints = 0;
    $totalCreditUnits = 0;
    $gpa = 0;

    if ($selectedSemester) {
        $grades = DB::table('tblassmain')
            ->join('tblsubject', 'tblassmain.subcode', '=', 'tblsubject.subcode')
            ->where('tblassmain.school_code', $school_code)
            ->where('tblassmain.deleted', '0')
            ->where('tblassmain.semester', $selectedSemester)
            ->where('tblassmain.student_no', $user->userid)
            ->select(
                'tblassmain.*', 
                'tblsubject.subname as subname',
                'tblsubject.credit' // Fetching credit unit
            )
            ->distinct()
            ->get();

        // Calculate grades & grade points
        $grades = $grades->map(function ($grade) use (&$totalGradePoints, &$totalCreditUnits) {
            $grade->letter_grade = $this->calculateGrade($grade->total_score);
            $grade->credit_grade = $this->gradePoint($grade->total_score, $grade->credit);

            // Accumulate grade points and credit units for GPA calculation
            $totalGradePoints += $grade->credit_grade;
            $totalCreditUnits += $grade->credit;

            return $grade;
        });

        
        if ($totalCreditUnits > 0) {
            $gpa = number_format($totalGradePoints / $totalCreditUnits, 2, '.', '');
        } else {
            $gpa = number_format(0, 2, '.', ''); // Ensure GPA is always in decimal format
        }
    }

    return view('modules.grades.index', compact('grades', 'semesters', 'selectedSemester', 'gpa'));
}




    private function calculateGrade($num)
    {
        if ($num >= 80) {
            return "A";
        } elseif ($num >= 75 && $num <= 79) {
            return "B+";
        } elseif ($num >= 70 && $num <= 74) {
            return "B";
        } elseif ($num >= 65 && $num <= 69) {
            return "C+";
        } elseif ($num >= 60 && $num <= 64) {
            return "C";
        } elseif ($num >= 55 && $num <= 59) {
            return "D+";
        } elseif ($num >= 50 && $num <= 54) {
            return "D";
        } elseif ($num >= 0 && $num <= 49) {
            return "E";
        } else {
            return "Invalid Grade";
        }
    }

    public function gradePoint($num, $credit)
    {
        if ($num >= 80) {
            return 4.00 * $credit;
        } elseif ($num >= 75 && $num <= 79) {
            return 3.50 * $credit;
        } elseif ($num >= 70 && $num <= 74) {
            return 3.00 * $credit;
        } elseif ($num >= 65 && $num <= 69) {
            return 2.50 * $credit;
        } elseif ($num >= 60 && $num <= 64) {
            return 2.00 * $credit;
        } elseif ($num >= 55 && $num <= 59) {
            return 1.50 * $credit;
        } elseif ($num >= 50 && $num <= 54) {
            return 1.00 * $credit;
        } elseif ($num >= 0 && $num <= 49) {
            return 0.00 * $credit;
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Assessment;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class AssessmentUploadController extends Controller
{
    public function showUploadForm()
    {
        $classes = DB::table('tblclass')->get();
        $subjects = DB::table('tblsubject')->get();
        
        return view('modules.newassessment.upload', [
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }



    public function processUpload(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            'class_code' => 'required',
            'subcode' => 'required',
            'term' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'msg' => $validator->errors()->first()
            ]);
        }
    
        try {
            // Get form data
            $classCode = $request->input('class_code');
            $subjectCode = $request->input('subcode');
            $term = $request->input('term');
            $schoolCode = auth()->user()->school_code ?? 'SCH001'; // Default or get from auth
    
            // Load Excel file
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
    
            // Ensure file is not empty
            if (empty($rows) || count($rows) < 2) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'The uploaded file is empty or does not contain enough data.'
                ]);
            }
    
            // Get header row
            $header = array_map('strtolower', array_map('trim', $rows[0]));
    
            // Define required columns and their possible names
            $requiredColumns = [
                'student_name' => ['student name', 'name', 'names'],
                'class_score' => ['class score', 'CLASS SCORE (40)'],
                'sat_1' => ['sat 1', 'paper1', 'SAT 1 (80)'],
                'sat_2' => ['sat 2', 'paper2', 'SAT 2 (100)'],
                'exams' => ['exams', 'exams score', 'exam', 'exam score','']
            ];
    
            // Map column indexes
            $columnIndexes = [];
            foreach ($requiredColumns as $key => $possibleNames) {
                foreach ($possibleNames as $name) {
                    if (($index = array_search(strtolower($name), $header)) !== false) {
                        $columnIndexes[$key] = $index;
                        break;
                    }
                }
            }
    
            // Check if all required columns are found
            foreach ($requiredColumns as $key => $names) {
                if (!isset($columnIndexes[$key])) {
                    return response()->json([
                        'ok' => false,
                        'msg' => "Missing required column: " . implode(' or ', $names)
                    ]);
                }
            }
    
            // Remove header row
            array_shift($rows);
    
            $processed = [];
            $errors = [];
            $success = 0;
    
            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;
    
                $studentName = trim($row[$columnIndexes['student_name']]);
    
                if (empty($studentName)) {
                    $errors[] = "Missing student name (Row " . ($index + 2) . ")";
                    continue;
                }
    
                // Find student in database
                $student = DB::table('tblstudent')
                    ->where(function($query) use ($studentName) {
                        $nameParts = explode(' ', $studentName);
                        if (count($nameParts) == 1) {
                            $query->where('fname', 'like', "%{$nameParts[0]}%")
                                  ->orWhere('mname', 'like', "%{$nameParts[0]}%")
                                  ->orWhere('lname', 'like', "%{$nameParts[0]}%");
                        } else {
                            $query->where(function($q) use ($nameParts) {
                                foreach ($nameParts as $part) {
                                    if (!empty($part)) {
                                        $q->orWhere('fname', 'like', "%{$part}%")
                                          ->orWhere('mname', 'like', "%{$part}%")
                                          ->orWhere('lname', 'like', "%{$part}%");
                                    }
                                }
                            });
                        }
                    })
                    ->where('current_class', $classCode)
                    ->first();
    
                if (!$student) {
                    $errors[] = "Student not found: {$studentName} (Row " . ($index + 2) . ")";
                    continue;
                }
    
                // Extract assessment data using mapped indexes
                // $classScore = isset($row[$columnIndexes['class_score']]) ? (float)$row[$columnIndexes['class_score']] : null;
                // $paper1 = isset($row[$columnIndexes['sat_1']]) ? (float)$row[$columnIndexes['sat_1']] : null;
                // $paper2 = isset($row[$columnIndexes['sat_2']]) ? (float)$row[$columnIndexes['sat_2']] : null;
                // $exams = isset($row[$columnIndexes['exams']]) ? (float)$row[$columnIndexes['exams']] : null;

                $classScore = isset($row[$columnIndexes['class_score']]) && $row[$columnIndexes['class_score']] !== '' ? (float)$row[$columnIndexes['class_score']] : 0;
$paper1 = isset($row[$columnIndexes['sat_1']]) && $row[$columnIndexes['sat_1']] !== '' ? (float)$row[$columnIndexes['sat_1']] : 0;
$paper2 = isset($row[$columnIndexes['sat_2']]) && $row[$columnIndexes['sat_2']] !== '' ? (float)$row[$columnIndexes['sat_2']] : 0;
$exams = isset($row[$columnIndexes['exams']]) && $row[$columnIndexes['exams']] !== '' ? (float)$row[$columnIndexes['exams']] : 0;

    
                // Calculate total score
                $totalClassScore = (($paper1 + $paper2 + $classScore) / 300) * 30;
                $exam70 = $exams*0.7;
                $totalGrade = $totalClassScore + $exam70;
    
                // Determine grade
                //$grade = $this->calculateGrade($totalGrade);
                ["grade" => $grade, "remarks" => $remarks] = $this->calculateGrade($totalGrade);
    
                // Check if assessment already exists
                $existingAssessment = DB::table('tblassmain_ai')
                    ->where('student_no', $student->student_no)
                    ->where('class_code', $classCode)
                    ->where('subcode', $subjectCode)
                    ->where('term', $term)
                    ->first();
    
                $transId = $existingAssessment->transid ?? null;
                $action = $existingAssessment ? 'update' : 'insert';
    
                $acyear = DB::table('tblacyear')
                    ->where('current_term', '1')
                    ->where('deleted', '0')
                    ->select('acyear_desc')
                    ->first();
    
                // Prepare data for insertion/update
                $newTransId = strtoupper(bin2hex(random_bytes(5)));
                $assessmentData = [
                    "transid" => $newTransId,
                    'school_code' => $schoolCode,
                    "acyear" => $acyear->acyear_desc,
                    'student_no' => $student->student_no,
                    'class_code' => $classCode,
                    'subcode' => $subjectCode,
                    'class_score' => $classScore,
                    'paper1' => $paper1,
                    'paper2' => $paper2,
                    'total_class_score' => $totalClassScore,
                    'exam' => $exams,
                    'exam70' => $exam70,
                    'total_grade' => $totalGrade,
                    'grade' => $grade,
                    "t_remarks" => $remarks,
                    "deleted" => "0",
                    'term' => $term,
                    "createuser" => auth()->user()->userid,
                    'modifyuser' => auth()->user()->userid ?? 1
                ];
    
                if ($action == 'insert') {
                    $assessmentData['createdate'] = now();
                    DB::table('tblassmain_ai')->insert($assessmentData);
                } else {
                    DB::table('tblassmain_ai')
                        ->where('transid', $transId)
                        ->update($assessmentData);
                }
    
                $success++;
            }
    
            return response()->json([
                'ok' => true,
                'msg' => "{$success} student assessments processed successfully",
                'errors' => $errors
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Error processing file: ' . $e->getMessage()
            ]);
        }
    }
    

    
    private function calculateGrade($score)
    {
        if ($score >= 90 && $score <= 100) {
            return ["grade" => "A*", "remarks" => "Excellent"];
        } elseif ($score >= 80 && $score <= 89) {
            return ["grade" => "A", "remarks" => "Excellent"];
        } elseif ($score >= 70 && $score <= 79) {
            return ["grade" => "B", "remarks" => "Very Good"];
        } elseif ($score >= 60 && $score <= 69) {
            return ["grade" => "C", "remarks" => "Good"];
        } elseif ($score >= 50 && $score <= 59) {
            return ["grade" => "D", "remarks" => "Credit"];
        } elseif ($score >= 40 && $score <= 49) {
            return ["grade" => "E", "remarks" => "Pass"];
        } elseif ($score >= 30 && $score <= 39) {
            return ["grade" => "F", "remarks" => "Fail"];
        } else {
            return ["grade" => "U", "remarks" => "Ungraded"];
        }
    }
}
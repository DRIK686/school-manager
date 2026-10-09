<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\StudentDocument;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['schoolClass','section','academicYear'])
            ->orderBy('first_name');

        if ($request->class_id) $query->where('class_id', $request->class_id);
        if ($request->section_id) $query->where('section_id', $request->section_id);
        if ($request->gender) $query->where('gender', $request->gender);
        if ($request->status === 'active') $query->where('is_active', true);
        if ($request->status === 'inactive') $query->where('is_active', false);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name','like',"%{$request->search}%")
                  ->orWhere('last_name','like',"%{$request->search}%")
                  ->orWhere('admission_no','like',"%{$request->search}%");
            });
        }

        $students = $query->paginate(20)->withQueryString();
        $classes  = SchoolClass::orderBy('numeric_order')->get();
        $sections = $request->class_id
            ? Section::where('class_id', $request->class_id)->get()
            : collect();

        return view('admin.students.index', compact('students','classes','sections'));
    }

    public function create()
    {
        $classes      = SchoolClass::orderBy('numeric_order')->get();
        $academicYear = AcademicYear::current();
        $admissionNo  = Student::generateAdmissionNo();
        return view('admin.students.create', compact('classes','academicYear','admissionNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'admission_no'   => 'nullable|string|max:50|unique:students,admission_no',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'other_name'     => 'nullable|string|max:100',
            'gender'         => 'required|in:male,female',
            'dob'            => 'nullable|date',
            'admission_date' => 'required|date',
            'class_id'       => 'required|exists:classes,id',
            'section_id'     => 'nullable|exists:sections,id',
            'blood_group'    => 'nullable|string|max:5',
            'religion'       => 'nullable|string|max:50',
            'nationality'    => 'nullable|string|max:50',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'previous_school'=> 'nullable|string|max:200',
            'previous_class' => 'nullable|string|max:50',
            'profile_photo'  => 'nullable|image|max:2048',
            // Parent
            'parent_name'    => 'required|string|max:200',
            'parent_relation'=> 'required|in:father,mother,guardian',
            'parent_phone'   => 'required|string|max:20',
            'parent_email'   => 'nullable|email|max:200',
            'parent_occupation' => 'nullable|string|max:100',
        ]);

        $data = $request->except(['_token','profile_photo','parent_name','parent_relation','parent_phone','parent_email','parent_occupation']);
        $data['admission_no']     = $request->filled('admission_no') ? $request->admission_no : Student::generateAdmissionNo();
        $data['academic_year_id'] = AcademicYear::current()?->id;

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('students/photos','public');
        }

        $student = Student::create($data);

        // Save parent
        $student->parents()->create([
            'relation'   => $request->parent_relation,
            'full_name'  => $request->parent_name,
            'phone'      => $request->parent_phone,
            'email'      => $request->parent_email,
            'occupation' => $request->parent_occupation,
        ]);

        return redirect()->route('admin.students.show', $student)
            ->with('success', "Student {$student->full_name} admitted successfully. ID: {$student->admission_no}");
    }

    public function show(Student $student)
    {
        $student->load(['schoolClass','section','academicYear','parents','documents']);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $classes      = SchoolClass::orderBy('numeric_order')->get();
        $sections     = Section::where('class_id', $student->class_id)->get();
        $academicYears= AcademicYear::orderByDesc('id')->get();
        return view('admin.students.edit', compact('student','classes','sections','academicYears'));
    }

    public function update(Request $request, Student $student)
    {
        $rules = [
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'gender'         => 'required|in:male,female',
            'admission_date' => 'required|date',
            'class_id'       => 'required|exists:classes,id',
            'profile_photo'  => 'nullable|image|max:2048',
        ];
        $rules += [
            'previous_school'   => 'nullable|string|max:200',
            'previous_class'    => 'nullable|string|max:100',
            'parent_name'       => 'nullable|required_with:parent_phone|string|max:200',
            'parent_phone'      => 'nullable|required_with:parent_name|string|max:20',
            'parent_relation'   => 'nullable|in:father,mother,guardian',
            'parent_email'      => 'nullable|email|max:200',
            'parent_occupation' => 'nullable|string|max:100',
        ];
        if (auth()->user()->isAdmin()) {
            $rules['admission_no'] = 'required|string|max:50|unique:students,admission_no,' . $student->id;
        }
        $request->validate($rules);

        $parentFields = ['parent_name','parent_relation','parent_phone','parent_email','parent_occupation'];
        $data = $request->except(array_merge(['_token','_method','profile_photo'], $parentFields));
        if (!auth()->user()->isAdmin()) {
            unset($data['admission_no']);
        }

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('students/photos','public');
        }

        $student->update($data);

        // Guardian: edit the first existing record (or create one if the student has none).
        if ($request->filled('parent_name')) {
            $attrs = [
                'relation'   => $request->parent_relation ?: 'guardian',
                'full_name'  => $request->parent_name,
                'phone'      => $request->parent_phone,
                'email'      => $request->parent_email ?: null,
                'occupation' => $request->parent_occupation ?: null,
            ];
            $parent = $student->parents()->orderBy('id')->first();
            $parent ? $parent->update($attrs) : $student->parents()->create($attrs);
        }

        return redirect()->route('admin.students.show', $student)->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student removed.');
    }
    public function withdraw(Request $request, Student $student)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $year = AcademicYear::current();
        if ($year) {
            \App\Models\StudentEnrollment::updateOrCreate(
                ['student_id' => $student->id, 'academic_year_id' => $year->id],
                ['class_id' => $student->class_id, 'section_id' => $student->section_id, 'status' => 'withdrawn', 'remarks' => $request->reason]
            );
        }
        $student->update(['is_active' => false]);
        ActivityLogger::log('student.withdraw', 'Withdrew ' . $student->full_name . ($request->reason ? ' — ' . $request->reason : ''), $student);
        return back()->with('success', $student->full_name . ' has been marked as withdrawn.');
    }
    public function reinstate(Student $student)
    {
        $student->update(['is_active' => true]);
        ActivityLogger::log('student.reinstate', 'Reinstated ' . $student->full_name, $student);
        return back()->with('success', $student->full_name . ' has been reinstated.');
    }

    public function uploadDocument(Request $request, Student $student)
    {
        $request->validate([
            'document_type' => 'required|string|max:100',
            'document_file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $path = $request->file('document_file')->store('students/documents','public');
        $student->documents()->create([
            'document_type' => $request->document_type,
            'file_path'     => $path,
            'file_name'     => $request->file('document_file')->getClientOriginalName(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function deleteDocument(Student $student, StudentDocument $document)
    {
        $document->delete();
        return back()->with('success', 'Document removed.');
    }

    public function getSections(SchoolClass $class)
    {
        return response()->json($class->sections()->get(['id','name']));
    }

    // ── Bulk Import ─────────────────────────────────────────────
    public function importForm()
    {
        $classes = SchoolClass::orderBy('numeric_order')->get();
        return view('admin.students.import', compact('classes'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'admission_no','first_name','last_name','other_name','gender','dob','admission_date',
            'class','section','blood_group','religion','nationality','phone','address',
            'previous_school','previous_class',
            'parent_name','parent_relation','parent_phone','parent_email','parent_occupation',
        ];
        $example = [
            '(leave blank to auto-generate, or enter existing ID)',
            'Ama','Mensah','Serwaa','female','2014-05-12','2026-09-01',
            'JHS 1','A','O+','Christianity','Ghanaian','0551234567','12 Ring Road, Accra',
            'St. Mary\'s Prep','Class 6',
            'Kofi Mensah','father','0551234568','kofi@example.com','Trader',
        ];

        $callback = function () use ($headers, $example) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, $example);
            fclose($out);
        };

        return response()->streamDownload($callback, 'student_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $required = ['first_name','last_name','gender','admission_date','class','parent_name','parent_relation','parent_phone'];
        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            fclose($handle);
            return back()->with('error', 'Template is missing required column(s): ' . implode(', ', $missing));
        }

        $year = AcademicYear::current();
        $classesByName = SchoolClass::get()->keyBy(fn($c) => strtolower(trim($c->name)));

        $succeeded = [];
        $failed = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue; // skip blank rows

            $row = array_pad($row, count($header), '');
            $data = array_combine($header, $row);
            $data = array_map(fn($v) => trim((string)$v), $data);

            try {
                // Required field checks
                foreach ($required as $field) {
                    if ($data[$field] === '') {
                        throw new \Exception("Missing required value: {$field}");
                    }
                }
                if (!in_array(strtolower($data['gender']), ['male','female'])) {
                    throw new \Exception("Gender must be 'male' or 'female'");
                }
                if (!in_array(strtolower($data['parent_relation']), ['father','mother','guardian'])) {
                    throw new \Exception("Parent relation must be father, mother, or guardian");
                }
                $admissionDate = \Carbon\Carbon::parse($data['admission_date']);
                $dob = $data['dob'] !== '' ? \Carbon\Carbon::parse($data['dob']) : null;

                $class = $classesByName->get(strtolower($data['class']));
                if (!$class) {
                    throw new \Exception("Class '{$data['class']}' not found. Check spelling against Classes & Sections.");
                }
                $sectionId = null;
                if ($data['section'] !== '') {
                    $section = $class->sections()->whereRaw('LOWER(name) = ?', [strtolower($data['section'])])->first();
                    if (!$section) {
                        throw new \Exception("Section '{$data['section']}' not found under class '{$class->name}'.");
                    }
                    $sectionId = $section->id;
                }

                if (!empty($data['admission_no'])) {
                    if (Student::withTrashed()->where('admission_no', $data['admission_no'])->exists()) {
                        throw new \Exception("Admission No '{$data['admission_no']}' is already in use by another student.");
                    }
                    $admissionNo = $data['admission_no'];
                } else {
                    $admissionNo = Student::generateAdmissionNo();
                }

                $student = Student::create([
                    'admission_no'     => $admissionNo,
                    'academic_year_id' => $year?->id,
                    'first_name'       => $data['first_name'],
                    'last_name'        => $data['last_name'],
                    'other_name'       => $data['other_name'] ?: null,
                    'gender'           => strtolower($data['gender']),
                    'dob'              => $dob,
                    'admission_date'   => $admissionDate,
                    'class_id'         => $class->id,
                    'section_id'       => $sectionId,
                    'blood_group'      => $data['blood_group'] ?: null,
                    'religion'         => $data['religion'] ?: null,
                    'nationality'      => $data['nationality'] ?: 'Ghanaian',
                    'phone'            => $data['phone'] ?: null,
                    'address'          => $data['address'] ?: null,
                    'previous_school'  => $data['previous_school'] ?: null,
                    'previous_class'   => $data['previous_class'] ?: null,
                ]);

                $student->parents()->create([
                    'relation'   => strtolower($data['parent_relation']),
                    'full_name'  => $data['parent_name'],
                    'phone'      => $data['parent_phone'],
                    'email'      => $data['parent_email'] ?: null,
                    'occupation' => $data['parent_occupation'] ?: null,
                ]);

                $succeeded[] = "Row {$rowNum}: {$student->full_name} ({$student->admission_no})";
            } catch (\Exception $e) {
                $failed[] = "Row {$rowNum}: " . $e->getMessage();
            }
        }
        fclose($handle);

        ActivityLogger::log('student.import', 'Bulk imported students: ' . count($succeeded) . ' succeeded, ' . count($failed) . ' failed.');
        return view('admin.students.import_result', compact('succeeded', 'failed'));
    }
}

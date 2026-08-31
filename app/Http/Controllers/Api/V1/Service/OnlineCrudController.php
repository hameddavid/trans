<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\RegistrationResult;
use App\Models\Course;
use App\Models\CollegeDept;
use App\Models\Setting;
use App\Models\CoursePassMark;
use Illuminate\Support\Facades\DB;

class OnlineCrudController extends Controller
{
    // ==================== STUDENTS (t_student_test) ====================

    /**
     * @OA\Get(
     *     path="/api/v1/service/students",
     *     operationId="serviceListStudents",
     *     tags={"Service Students"},
     *     summary="List students",
     *     description="Retrieve a paginated list of students with optional search, programme, and status filters.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, description="Search by matric number, surname, or firstname", @OA\Schema(type="string")),
     *     @OA\Parameter(name="prog_code", in="query", required=false, description="Filter by programme code", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by student status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (max 100)", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated student list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function listStudents(Request $request)
    {
        $query = Student::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('matric_number', 'like', "%{$search}%")
                  ->orWhere('SURNAME', 'like', "%{$search}%")
                  ->orWhere('FIRSTNAME', 'like', "%{$search}%");
            });
        }
        if ($progCode = $request->input('prog_code')) {
            $query->where('prog_code', $progCode);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $data = $query->orderBy('matric_number')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/service/students/{id}",
     *     operationId="serviceShowStudent",
     *     tags={"Service Students"},
     *     summary="Show a student",
     *     description="Retrieve a single student record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Student ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Student details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function showStudent($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $student]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service/students",
     *     operationId="serviceCreateStudent",
     *     tags={"Service Students"},
     *     summary="Create or update a student",
     *     description="Create a new student or update an existing one by matric number (upsert).",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matric_number"},
     *             @OA\Property(property="matric_number", type="string", maxLength=25, example="UG/2019/1234")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Student saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Student saved"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createStudent(Request $request)
    {
        $request->validate([
            'matric_number' => 'required|string|max:25',
        ]);

        $student = Student::updateOrCreate(
            ['matric_number' => strtoupper(trim($request->matric_number))],
            $request->only((new Student)->getFillable())
        );

        return response()->json(['status' => 'success', 'message' => 'Student saved', 'data' => $student], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/service/students/{id}",
     *     operationId="serviceUpdateStudent",
     *     tags={"Service Students"},
     *     summary="Update a student",
     *     description="Update an existing student record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Student ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Student updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Student updated"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function updateStudent(Request $request, $id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
        }

        $student->update($request->only((new Student)->getFillable()));
        return response()->json(['status' => 'success', 'message' => 'Student updated', 'data' => $student]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service/students/bulk-status",
     *     operationId="serviceBulkUpdateStudentStatus",
     *     tags={"Service Students"},
     *     summary="Bulk update student statuses",
     *     description="Update the status of multiple students at once by matric numbers.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matric_numbers","status"},
     *             @OA\Property(property="matric_numbers", type="array", @OA\Items(type="string"), example={"UG/2019/1234","UG/2019/5678"}),
     *             @OA\Property(property="status", type="string", example="graduated"),
     *             @OA\Property(property="session_graduated", type="string", nullable=true, example="2022/2023")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Bulk update result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="total_submitted", type="integer"),
     *             @OA\Property(property="total_updated", type="integer"),
     *             @OA\Property(property="not_found", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function bulkUpdateStudentStatus(Request $request)
    {
        $request->validate([
            'matric_numbers' => 'required|array|min:1',
            'matric_numbers.*' => 'required|string',
            'status' => 'required|string',
        ]);

        $matrics = array_map(function ($m) {
            return strtoupper(trim($m));
        }, $request->matric_numbers);

        $updateData = ['status' => $request->status];
        if ($request->has('session_graduated')) {
            $updateData['session_graduated'] = $request->session_graduated;
        }

        $foundMatrics = Student::whereIn('matric_number', $matrics)
            ->pluck('matric_number')
            ->map(fn ($m) => strtoupper($m))
            ->toArray();

        $notFound = array_values(array_diff($matrics, $foundMatrics));

        $updated = 0;
        if (count($foundMatrics) > 0) {
            $updated = Student::whereIn('matric_number', $foundMatrics)->update($updateData);
        }

        return response()->json([
            'status' => 'success',
            'message' => "{$updated} student(s) updated",
            'total_submitted' => count($matrics),
            'total_updated' => $updated,
            'not_found' => $notFound,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/service/students/{id}",
     *     operationId="serviceDeleteStudent",
     *     tags={"Service Students"},
     *     summary="Delete a student",
     *     description="Delete a student record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Student ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Student deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Student deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function deleteStudent($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Student not found'], 404);
        }
        $student->delete();
        return response()->json(['status' => 'success', 'message' => 'Student deleted']);
    }

    // ==================== REGISTRATIONS ====================

    /**
     * @OA\Get(
     *     path="/api/v1/service/registrations",
     *     operationId="serviceListRegistrations",
     *     tags={"Service Registrations"},
     *     summary="List registrations",
     *     description="Retrieve a paginated list of course registrations with optional filters.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="matric_number", in="query", required=false, description="Filter by matric number", @OA\Schema(type="string")),
     *     @OA\Parameter(name="session_id", in="query", required=false, description="Filter by session", @OA\Schema(type="string")),
     *     @OA\Parameter(name="semester", in="query", required=false, description="Filter by semester", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="course_code", in="query", required=false, description="Filter by course code", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (max 100)", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated registration list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function listRegistrations(Request $request)
    {
        $query = RegistrationResult::query();

        if ($matric = $request->input('matric_number')) {
            $query->where('matric_number', 'like', "%{$matric}%");
        }
        if ($session = $request->input('session_id')) {
            $query->where('session_id', $session);
        }
        if ($semester = $request->input('semester')) {
            $query->where('semester', $semester);
        }
        if ($courseCode = $request->input('course_code')) {
            $query->where('course_code', 'like', "%{$courseCode}%");
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $data = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/service/registrations/{id}",
     *     operationId="serviceShowRegistration",
     *     tags={"Service Registrations"},
     *     summary="Show a registration",
     *     description="Retrieve a single course registration record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Registration ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Registration details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Registration not found")
     * )
     */
    public function showRegistration($id)
    {
        $reg = RegistrationResult::find($id);
        if (!$reg) {
            return response()->json(['status' => 'error', 'message' => 'Registration not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $reg]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service/registrations",
     *     operationId="serviceCreateRegistration",
     *     tags={"Service Registrations"},
     *     summary="Create or update a registration",
     *     description="Create a new course registration or update an existing one (upsert by matric_number, session_id, semester, course_code).",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"matric_number","session_id","semester","course_code"},
     *             @OA\Property(property="matric_number", type="string", example="UG/2019/1234"),
     *             @OA\Property(property="session_id", type="string", example="2022/2023"),
     *             @OA\Property(property="semester", type="integer", example=1),
     *             @OA\Property(property="course_code", type="string", example="CSC101")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registration saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Registration saved"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createRegistration(Request $request)
    {
        $request->validate([
            'matric_number' => 'required|string',
            'session_id' => 'required|string',
            'semester' => 'required|integer',
            'course_code' => 'required|string',
        ]);

        $reg = RegistrationResult::updateOrCreate(
            [
                'matric_number' => strtoupper(trim($request->matric_number)),
                'session_id' => $request->session_id,
                'semester' => $request->semester,
                'course_code' => strtoupper(trim($request->course_code)),
            ],
            $request->only((new RegistrationResult)->getFillable())
        );

        return response()->json(['status' => 'success', 'message' => 'Registration saved', 'data' => $reg], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/service/registrations/{id}",
     *     operationId="serviceUpdateRegistration",
     *     tags={"Service Registrations"},
     *     summary="Update a registration",
     *     description="Update an existing course registration record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Registration ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Registration updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Registration updated"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Registration not found")
     * )
     */
    public function updateRegistration(Request $request, $id)
    {
        $reg = RegistrationResult::find($id);
        if (!$reg) {
            return response()->json(['status' => 'error', 'message' => 'Registration not found'], 404);
        }

        $reg->update($request->only((new RegistrationResult)->getFillable()));
        return response()->json(['status' => 'success', 'message' => 'Registration updated', 'data' => $reg]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/service/registrations/{id}",
     *     operationId="serviceDeleteRegistration",
     *     tags={"Service Registrations"},
     *     summary="Delete a registration",
     *     description="Delete a course registration record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Registration ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Registration deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Registration deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Registration not found")
     * )
     */
    public function deleteRegistration($id)
    {
        $reg = RegistrationResult::find($id);
        if (!$reg) {
            return response()->json(['status' => 'error', 'message' => 'Registration not found'], 404);
        }
        $reg->delete();
        return response()->json(['status' => 'success', 'message' => 'Registration deleted']);
    }

    // ==================== COURSES (t_course) ====================

    /**
     * @OA\Get(
     *     path="/api/v1/service/courses",
     *     operationId="serviceListCourses",
     *     tags={"Service Courses"},
     *     summary="List courses",
     *     description="Retrieve a paginated list of courses with optional search filter.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, description="Search by course code or title", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (max 100)", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated course list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function listCourses(Request $request)
    {
        $query = Course::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('course_code', 'like', "%{$search}%")
                  ->orWhere('course_title', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $data = $query->orderBy('course_code')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service/courses",
     *     operationId="serviceCreateCourse",
     *     tags={"Service Courses"},
     *     summary="Create or update a course",
     *     description="Create a new course or update an existing one by course code (upsert).",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_code"},
     *             @OA\Property(property="course_code", type="string", maxLength=45, example="CSC101")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Course saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Course saved"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createCourse(Request $request)
    {
        $request->validate([
            'course_code' => 'required|string|max:45',
        ]);

        $course = Course::updateOrCreate(
            ['course_code' => strtoupper(trim($request->course_code))],
            $request->only((new Course)->getFillable())
        );

        return response()->json(['status' => 'success', 'message' => 'Course saved', 'data' => $course], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/service/courses/{id}",
     *     operationId="serviceUpdateCourse",
     *     tags={"Service Courses"},
     *     summary="Update a course",
     *     description="Update an existing course record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Course ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Course updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Course updated"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function updateCourse(Request $request, $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Course not found'], 404);
        }

        $course->update($request->only((new Course)->getFillable()));
        return response()->json(['status' => 'success', 'message' => 'Course updated', 'data' => $course]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/service/courses/{id}",
     *     operationId="serviceDeleteCourse",
     *     tags={"Service Courses"},
     *     summary="Delete a course",
     *     description="Delete a course record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Course ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Course deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Course deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function deleteCourse($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Course not found'], 404);
        }
        $course->delete();
        return response()->json(['status' => 'success', 'message' => 'Course deleted']);
    }

    // ==================== DEPARTMENTS (t_college_dept) ====================

    /**
     * @OA\Get(
     *     path="/api/v1/service/departments",
     *     operationId="serviceListDepartments",
     *     tags={"Service Departments"},
     *     summary="List departments",
     *     description="Retrieve a paginated list of departments with optional search filter.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, description="Search by prog code, programme, department, or college", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (max 100)", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated department list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function listDepartments(Request $request)
    {
        $query = CollegeDept::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('prog_code', 'like', "%{$search}%")
                  ->orWhere('programme', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('college', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $data = $query->orderBy('college')->orderBy('department')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service/departments",
     *     operationId="serviceCreateDepartment",
     *     tags={"Service Departments"},
     *     summary="Create or update a department",
     *     description="Create a new department or update an existing one by programme code (upsert).",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"prog_code"},
     *             @OA\Property(property="prog_code", type="string", maxLength=10, example="CSC")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Department saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Department saved"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createDepartment(Request $request)
    {
        $request->validate([
            'prog_code' => 'required|string|max:10',
        ]);

        $dept = CollegeDept::updateOrCreate(
            ['prog_code' => $request->prog_code],
            $request->only((new CollegeDept)->getFillable())
        );

        return response()->json(['status' => 'success', 'message' => 'Department saved', 'data' => $dept], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/service/departments/{id}",
     *     operationId="serviceUpdateDepartment",
     *     tags={"Service Departments"},
     *     summary="Update a department",
     *     description="Update an existing department record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Department ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Department updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Department updated"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Department not found")
     * )
     */
    public function updateDepartment(Request $request, $id)
    {
        $dept = CollegeDept::find($id);
        if (!$dept) {
            return response()->json(['status' => 'error', 'message' => 'Department not found'], 404);
        }

        $dept->update($request->only((new CollegeDept)->getFillable()));
        return response()->json(['status' => 'success', 'message' => 'Department updated', 'data' => $dept]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/service/departments/{id}",
     *     operationId="serviceDeleteDepartment",
     *     tags={"Service Departments"},
     *     summary="Delete a department",
     *     description="Delete a department record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Department ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Department deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Department deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Department not found")
     * )
     */
    public function deleteDepartment($id)
    {
        $dept = CollegeDept::find($id);
        if (!$dept) {
            return response()->json(['status' => 'error', 'message' => 'Department not found'], 404);
        }
        $dept->delete();
        return response()->json(['status' => 'success', 'message' => 'Department deleted']);
    }

    // ==================== SETTINGS ====================

    /**
     * @OA\Get(
     *     path="/api/v1/service/settings",
     *     operationId="serviceListSettings",
     *     tags={"Service Settings"},
     *     summary="List settings",
     *     description="Retrieve a paginated list of settings with optional session filter.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="session", in="query", required=false, description="Filter by session", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (max 100)", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated settings list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function listSettings(Request $request)
    {
        $query = Setting::query();

        if ($session = $request->input('session')) {
            $query->where('session', $session);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $data = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service/settings",
     *     operationId="serviceCreateSetting",
     *     tags={"Service Settings"},
     *     summary="Create a setting",
     *     description="Create a new setting record.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"semester","session"},
     *             @OA\Property(property="semester", type="integer", example=1),
     *             @OA\Property(property="session", type="string", maxLength=9, example="2022/2023")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Setting created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Setting created"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createSetting(Request $request)
    {
        $request->validate([
            'semester' => 'required|integer',
            'session' => 'required|string|max:9',
        ]);

        $data = $request->only((new Setting)->getFillable());
        if (!isset($data['status'])) {
            $data['status'] = '';
        }
        $setting = Setting::create($data);
        return response()->json(['status' => 'success', 'message' => 'Setting created', 'data' => $setting], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/service/settings/{id}",
     *     operationId="serviceUpdateSetting",
     *     tags={"Service Settings"},
     *     summary="Update a setting",
     *     description="Update an existing setting record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Setting ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Setting updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Setting updated"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Setting not found")
     * )
     */
    public function updateSetting(Request $request, $id)
    {
        $setting = Setting::find($id);
        if (!$setting) {
            return response()->json(['status' => 'error', 'message' => 'Setting not found'], 404);
        }

        $setting->update($request->only((new Setting)->getFillable()));
        return response()->json(['status' => 'success', 'message' => 'Setting updated', 'data' => $setting]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/service/settings/{id}",
     *     operationId="serviceDeleteSetting",
     *     tags={"Service Settings"},
     *     summary="Delete a setting",
     *     description="Delete a setting record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Setting ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Setting deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Setting deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Setting not found")
     * )
     */
    public function deleteSetting($id)
    {
        $setting = Setting::find($id);
        if (!$setting) {
            return response()->json(['status' => 'error', 'message' => 'Setting not found'], 404);
        }
        $setting->delete();
        return response()->json(['status' => 'success', 'message' => 'Setting deleted']);
    }

    // ==================== COURSE PASS MARKS (ug_course_with_pass_mark) ====================

    /**
     * @OA\Get(
     *     path="/api/v1/service/pass-marks",
     *     operationId="serviceListPassMarks",
     *     tags={"Service Pass Marks"},
     *     summary="List pass marks",
     *     description="Retrieve a paginated list of course pass marks with optional search filter.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, description="Search by course code or programme", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (max 100)", @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated pass marks list",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     )
     * )
     */
    public function listPassMarks(Request $request)
    {
        $query = CoursePassMark::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('course_code', 'like', "%{$search}%")
                  ->orWhere('programme', 'like', "%{$search}%");
            });
        }
        $query->where(function ($q) {
            $q->where('deleted', 'N')->orWhereNull('deleted');
        });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $data = $query->orderBy('course_code')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data->items(),
            'total' => $data->total(),
            'page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service/pass-marks",
     *     operationId="serviceCreatePassMark",
     *     tags={"Service Pass Marks"},
     *     summary="Create or update a pass mark",
     *     description="Create a new course pass mark or update an existing one (upsert by course_code and programme).",
     *     security={{"serviceApiKey":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"course_code","pass_mark","programme"},
     *             @OA\Property(property="course_code", type="string", maxLength=45, example="CSC101"),
     *             @OA\Property(property="pass_mark", type="number", example=40),
     *             @OA\Property(property="programme", type="string", maxLength=191, example="Computer Science")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Pass mark saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Pass mark saved"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createPassMark(Request $request)
    {
        $request->validate([
            'course_code' => 'required|string|max:45',
            'pass_mark' => 'required',
            'programme' => 'required|string|max:191',
        ]);

        $pm = CoursePassMark::updateOrCreate(
            [
                'course_code' => strtoupper(trim($request->course_code)),
                'programme' => $request->programme,
            ],
            array_merge($request->only((new CoursePassMark)->getFillable()), ['deleted' => 'N'])
        );

        return response()->json(['status' => 'success', 'message' => 'Pass mark saved', 'data' => $pm], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/service/pass-marks/{id}",
     *     operationId="serviceUpdatePassMark",
     *     tags={"Service Pass Marks"},
     *     summary="Update a pass mark",
     *     description="Update an existing course pass mark record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Pass mark ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=200, description="Pass mark updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Pass mark updated"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Pass mark not found")
     * )
     */
    public function updatePassMark(Request $request, $id)
    {
        $pm = CoursePassMark::find($id);
        if (!$pm) {
            return response()->json(['status' => 'error', 'message' => 'Pass mark not found'], 404);
        }

        $pm->update($request->only((new CoursePassMark)->getFillable()));
        return response()->json(['status' => 'success', 'message' => 'Pass mark updated', 'data' => $pm]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/service/pass-marks/{id}",
     *     operationId="serviceDeletePassMark",
     *     tags={"Service Pass Marks"},
     *     summary="Delete a pass mark",
     *     description="Soft-delete a course pass mark record by ID.",
     *     security={{"serviceApiKey":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Pass mark ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Pass mark deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Pass mark deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Pass mark not found")
     * )
     */
    public function deletePassMark($id)
    {
        $pm = CoursePassMark::find($id);
        if (!$pm) {
            return response()->json(['status' => 'error', 'message' => 'Pass mark not found'], 404);
        }
        $pm->update(['deleted' => 'Y']);
        return response()->json(['status' => 'success', 'message' => 'Pass mark deleted']);
    }
}

<?php
/**
 * ReliaWork2 TechVocController
 * Handles TECH-VOC Supervisor dashboard, classes, students, attendance
 */

class TechVocController
{
    private TechVocModel $model;

    public function __construct()
    {
        $this->model = new TechVocModel();
    }

    // GET /techvoc/dashboard
    public function dashboard(): void
    {
        requireRole('techvoc_supervisor');
        $classes   = $this->model->getAllClasses();
        $pageTitle = 'TECH-VOC Supervisor Dashboard';
        $success   = getFlash('success');
        $error     = getFlash('error');
        include VIEW_PATH . '/techvoc/dashboard.php';
    }

    // GET /techvoc/class/{id}
    public function classDetail(int $id): void
    {
        requireRole('techvoc_supervisor');
        $class    = $this->model->findClass($id);
        if (!$class) { flash('error', 'Class not found.'); redirect(APP_URL . '/techvoc/dashboard'); }
        $students  = $this->model->getStudentsByClass($id);
        $sundays   = $this->model->getSundaySessions($id);
        $pageTitle = $class['name'];
        $success   = getFlash('success');
        $error     = getFlash('error');
        include VIEW_PATH . '/techvoc/class_detail.php';
    }

    // POST /techvoc/class/{id}/add-student
    public function addStudent(int $classId): void
    {
        requireRole('techvoc_supervisor');
        verifyCsrf();

        $lastname  = trim($_POST['lastname']  ?? '');
        $firstname = trim($_POST['firstname'] ?? '');

        if (empty($lastname) || empty($firstname)) {
            flash('error', 'Last name and first name are required.');
            redirect(APP_URL . '/techvoc/class/' . $classId);
        }

        $this->model->addStudent([
            'techvoc_class_id' => $classId,
            'lastname'         => $lastname,
            'firstname'        => $firstname,
            'middlename'       => trim($_POST['middlename']      ?? '') ?: null,
            'age'              => (int)($_POST['age']            ?? 0) ?: null,
            'gender'           => $_POST['gender']               ?? null,
            'address'          => trim($_POST['address']         ?? '') ?: null,
            'contact_number'   => trim($_POST['contact_number']  ?? '') ?: null,
            'email'            => trim($_POST['email']           ?? '') ?: null,
        ]);

        $class = $this->model->findClass($classId);
        auditLog('add_student', 'techvoc', "Added student {$lastname}, {$firstname} to {$class['name']}");
        flash('success', "Student {$lastname}, {$firstname} added successfully.");
        redirect(APP_URL . '/techvoc/class/' . $classId);
    }

    // POST /techvoc/class/{id}/delete-student/{studentId}  (via POST _method)
    public function deleteStudent(int $classId): void
    {
        requireRole('techvoc_supervisor');
        verifyCsrf();
        $studentId = (int)($_POST['student_id'] ?? 0);
        $student   = $this->model->findStudent($studentId);
        if ($student && $student['techvoc_class_id'] == $classId) {
            $this->model->deleteStudent($studentId);
            auditLog('delete_student', 'techvoc', "Removed student ID {$studentId} from class ID {$classId}");
            flash('success', 'Student removed.');
        }
        redirect(APP_URL . '/techvoc/class/' . $classId);
    }

    // GET /techvoc/class/{id}/attendance?date=YYYY-MM-DD
    public function attendance(int $classId): void
    {
        requireRole('techvoc_supervisor');
        $class = $this->model->findClass($classId);
        if (!$class) { flash('error', 'Class not found.'); redirect(APP_URL . '/techvoc/dashboard'); }

        $sundays     = $this->model->getSundaySessions($classId);
        $selectedDate = $_GET['date'] ?? ($sundays[0] ?? date('Y-m-d'));
        $records     = $this->model->getAttendanceByDate($classId, $selectedDate);
        $summary     = $this->model->getAttendanceSummary($classId);
        $pageTitle   = 'Attendance — ' . $class['name'];
        $success     = getFlash('success');
        include VIEW_PATH . '/techvoc/attendance.php';
    }

    // POST /techvoc/class/{id}/attendance/save
    public function saveAttendance(int $classId): void
    {
        requireRole('techvoc_supervisor');
        verifyCsrf();
        $date    = $_POST['session_date'] ?? '';
        $records = $_POST['attendance']   ?? [];

        if (empty($date) || empty($records)) {
            flash('error', 'Date and attendance records are required.');
            redirect(APP_URL . '/techvoc/class/' . $classId . '/attendance');
        }

        $this->model->saveAttendance($classId, $date, $records, currentUser()['id']);
        auditLog('save_attendance', 'techvoc', "Saved attendance for class {$classId} on {$date}");
        flash('success', "Attendance for {$date} saved successfully.");
        redirect(APP_URL . '/techvoc/class/' . $classId . '/attendance?date=' . $date);
    }
}

<?php
/**
 * ReliaWork2 TechVocModel
 * Handles techvoc_classes, techvoc_students, techvoc_attendance
 */

class TechVocModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Classes ──────────────────────────────────────────────────────────────

    public function getAllClasses(): array
    {
        return $this->db->fetchAll(
            "SELECT tc.*,
                    COUNT(DISTINCT ts.id) AS student_count,
                    u.name AS created_by_name
             FROM techvoc_classes tc
             LEFT JOIN techvoc_students ts ON ts.techvoc_class_id = tc.id AND ts.status = 'active'
             LEFT JOIN users u ON u.id = tc.created_by
             GROUP BY tc.id
             ORDER BY tc.name ASC"
        );
    }

    public function findClass(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT tc.*, COUNT(DISTINCT ts.id) AS student_count
             FROM techvoc_classes tc
             LEFT JOIN techvoc_students ts ON ts.techvoc_class_id = tc.id AND ts.status = 'active'
             WHERE tc.id = ?
             GROUP BY tc.id",
            [$id]
        );
    }

    // ─── Students ─────────────────────────────────────────────────────────────

    public function getStudentsByClass(int $classId, string $status = ''): array
    {
        $sql    = "SELECT * FROM techvoc_students WHERE techvoc_class_id = ?";
        $params = [$classId];
        if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
        $sql .= " ORDER BY lastname ASC, firstname ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function findStudent(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT ts.*, tc.name AS class_name
             FROM techvoc_students ts
             JOIN techvoc_classes tc ON tc.id = ts.techvoc_class_id
             WHERE ts.id = ?",
            [$id]
        );
    }

    public function addStudent(array $data): int
    {
        $this->db->execute(
            "INSERT INTO techvoc_students
             (techvoc_class_id, lastname, firstname, middlename, age, gender, address, contact_number, email, status, enrolled_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())",
            [
                $data['techvoc_class_id'],
                $data['lastname'],
                $data['firstname'],
                $data['middlename']     ?? null,
                $data['age']            ?? null,
                $data['gender']         ?? null,
                $data['address']        ?? null,
                $data['contact_number'] ?? null,
                $data['email']          ?? null,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function updateStudent(int $id, array $data): void
    {
        $allowed = ['lastname','firstname','middlename','age','gender','address','contact_number','email','status'];
        $sets = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) { $sets[] = "$f = ?"; $params[] = $data[$f]; }
        }
        if (empty($sets)) return;
        $params[] = $id;
        $this->db->execute("UPDATE techvoc_students SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    public function deleteStudent(int $id): void
    {
        $this->db->execute("DELETE FROM techvoc_students WHERE id = ?", [$id]);
    }

    public function countStudents(int $classId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM techvoc_students WHERE techvoc_class_id = ? AND status = 'active'",
            [$classId]
        );
    }

    // ─── Sunday Sessions ──────────────────────────────────────────────────────

    /**
     * Generate all Sunday dates between start_date and end_date for a class.
     */
    public function getSundaySessions(int $classId): array
    {
        $class = $this->findClass($classId);
        if (!$class || !$class['start_date']) return [];

        $start   = new DateTime($class['start_date']);
        $end     = new DateTime($class['end_date'] ?? date('Y-m-d', strtotime($class['start_date'] . ' +6 months')));
        $today   = new DateTime();
        $sundays = [];

        // Move start to first Sunday on or after start_date
        $dow = (int)$start->format('w'); // 0=Sun
        if ($dow !== 0) $start->modify('next Sunday');

        while ($start <= $end) {
            $sundays[] = $start->format('Y-m-d');
            $start->modify('+7 days');
        }
        return $sundays;
    }

    // ─── Attendance ───────────────────────────────────────────────────────────

    public function getAttendanceByDate(int $classId, string $date): array
    {
        return $this->db->fetchAll(
            "SELECT ts.id AS student_id, ts.lastname, ts.firstname, ts.middlename,
                    COALESCE(ta.status, 'absent') AS attendance_status,
                    ta.notes
             FROM techvoc_students ts
             LEFT JOIN techvoc_attendance ta
                ON ta.student_id = ts.id AND ta.session_date = ?
             WHERE ts.techvoc_class_id = ? AND ts.status = 'active'
             ORDER BY ts.lastname ASC, ts.firstname ASC",
            [$date, $classId]
        );
    }

    public function saveAttendance(int $classId, string $date, array $records, int $recordedBy): void
    {
        foreach ($records as $studentId => $status) {
            $studentId = (int)$studentId;
            $this->db->execute(
                "INSERT INTO techvoc_attendance (techvoc_class_id, student_id, session_date, status, recorded_by, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE status = VALUES(status), recorded_by = VALUES(recorded_by)",
                [$classId, $studentId, $date, $status, $recordedBy]
            );
        }
    }

    public function getAttendanceSummary(int $classId): array
    {
        return $this->db->fetchAll(
            "SELECT ts.id, ts.lastname, ts.firstname,
                    COUNT(ta.id) AS total_sessions,
                    SUM(ta.status = 'present') AS present,
                    SUM(ta.status = 'absent')  AS absent,
                    SUM(ta.status = 'late')    AS late
             FROM techvoc_students ts
             LEFT JOIN techvoc_attendance ta ON ta.student_id = ts.id AND ta.techvoc_class_id = ?
             WHERE ts.techvoc_class_id = ? AND ts.status = 'active'
             GROUP BY ts.id
             ORDER BY ts.lastname ASC",
            [$classId, $classId]
        );
    }
}

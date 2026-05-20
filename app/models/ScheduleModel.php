<?php
/**
 * ReliaWork2 ScheduleModel
 */

class ScheduleModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT s.*, u.name AS created_by_name
             FROM schedule_of_events s
             LEFT JOIN users u ON u.id = s.created_by
             WHERE s.id = ?",
            [$id]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT s.*, u.name AS created_by_name
                   FROM schedule_of_events s
                   LEFT JOIN users u ON u.id = s.created_by
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['from_date'])) {
            $sql .= " AND s.event_date >= ?";
            $params[] = $filters['from_date'];
        }

        $sql .= " ORDER BY s.event_date ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            "INSERT INTO schedule_of_events (title, event_date, event_time, venue, description, status, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $data['title'],
                $data['event_date'],
                $data['event_time'] ?? null,
                $data['venue'] ?? null,
                $data['description'] ?? null,
                $data['status'] ?? 'available',
                $data['created_by'],
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['title', 'event_date', 'event_time', 'venue', 'description', 'status'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($sets)) return false;
        $params[] = $id;
        $this->db->execute("UPDATE schedule_of_events SET " . implode(', ', $sets) . " WHERE id = ?", $params);
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM schedule_of_events WHERE id = ?", [$id]);
        return true;
    }

    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $all    = $this->findAll($filters);
        $total  = count($all);
        $data   = array_slice($all, $offset, $perPage);
        return [
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Check if a date is already booked.
     */
    public function isDateBooked(string $date, ?int $excludeId = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM schedule_of_events WHERE event_date = ? AND status = 'booked'";
        $params = [$date];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        return (int)$this->db->fetchColumn($sql, $params) > 0;
    }

    /**
     * Check if a date has any event (available or booked).
     */
    public function isDateAvailable(string $date): bool
    {
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM schedule_of_events WHERE event_date = ? AND status IN ('available','booked')",
            [$date]
        );
        return $count === 0;
    }

    /**
     * Mark a schedule date as booked.
     */
    public function markBooked(string $date): void
    {
        $this->db->execute(
            "UPDATE schedule_of_events SET status = 'booked' WHERE event_date = ? AND status = 'available'",
            [$date]
        );
    }

    /**
     * Get all booked dates (status = 'booked') as an array of 'YYYY-MM-DD' strings.
     * Also includes dates from approved job_fair_requests.
     */
    public function getBookedDates(): array
    {
        // Dates from schedule_of_events marked booked
        $scheduleDates = $this->db->fetchAll(
            "SELECT event_date FROM schedule_of_events WHERE status = 'booked'"
        );

        // Dates from approved job fair requests
        $requestDates = $this->db->fetchAll(
            "SELECT requested_date AS event_date FROM job_fair_requests WHERE status = 'approved'"
        );

        $all = array_merge($scheduleDates, $requestDates);
        return array_unique(array_column($all, 'event_date'));
    }
}

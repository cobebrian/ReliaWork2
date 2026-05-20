<?php
/**
 * ReliaWork2 UserModel
 */

class UserModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT u.*, p.phone, p.address, p.organization, p.position
             FROM users u
             LEFT JOIN profiles p ON p.user_id = u.id
             WHERE u.id = ?",
            [$id]
        );
    }

    public function findByEmail(string $email): array|false
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    public function findAll(array $filters = []): array
    {
        $sql    = "SELECT u.*, p.phone, p.organization FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.lastname LIKE ? OR u.firstname LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY u.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        // Build full name from parts for the legacy `name` column
        $fullName = trim(
            ($data['lastname']   ?? '') . ' ' .
            ($data['firstname']  ?? '') . ' ' .
            ($data['middlename'] ?? '')
        );
        if (empty($fullName)) $fullName = $data['name'] ?? '';

        $this->db->execute(
            "INSERT INTO users (name, lastname, firstname, middlename, email, password, role, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $fullName,
                $data['lastname']   ?? null,
                $data['firstname']  ?? null,
                $data['middlename'] ?? null,
                $data['email'],
                $data['password'],
                $data['role']   ?? null,
                $data['status'] ?? 'pending',
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets   = [];
        $params = [];

        $allowed = ['name', 'email', 'password', 'role', 'status'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($sets)) return false;

        $params[] = $id;
        $this->db->execute(
            "UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?",
            $params
        );
        return true;
    }

    public function delete(int $id): bool
    {
        $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);
        return true;
    }

    public function paginate(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $sql    = "SELECT u.*, p.phone, p.organization FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $like = '%' . $filters['search'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $countSql = "SELECT COUNT(*) FROM users u WHERE 1=1";
        $countParams = $params;

        $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return [
            'data'       => $this->db->fetchAll($sql, $params),
            'total'      => (int)$this->db->fetchColumn($countSql, $countParams),
            'page'       => $page,
            'per_page'   => $perPage,
            'last_page'  => (int)ceil($this->db->fetchColumn($countSql, $countParams) / $perPage),
        ];
    }

    public function countByStatus(string $status): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE status = ?",
            [$status]
        );
    }

    public function countByRole(string $role): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE role = ?",
            [$role]
        );
    }

    public function createProfile(int $userId, array $data): void
    {
        // Upsert profile
        $this->db->execute(
            "INSERT INTO profiles (user_id, phone, address, organization, position, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE phone=VALUES(phone), address=VALUES(address),
             organization=VALUES(organization), position=VALUES(position)",
            [
                $userId,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['organization'] ?? null,
                $data['position'] ?? null,
            ]
        );
    }
}

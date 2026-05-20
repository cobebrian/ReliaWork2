<?php
/**
 * ReliaWork2 NotificationModel
 */

class NotificationModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a notification for a user.
     */
    public function create(int $userId, string $type, string $title, string $message = '', string $link = ''): int
    {
        $this->db->execute(
            "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
             VALUES (?, ?, ?, ?, ?, 0, NOW())",
            [$userId, $type, $title, $message, $link]
        );
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC",
            [$userId]
        );
    }

    /**
     * Get all notifications for a user (paginated).
     */
    public function getAll(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    /**
     * Count unread notifications.
     */
    public function countUnread(int $userId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }

    /**
     * Mark a notification as read.
     */
    public function markRead(int $id, int $userId): void
    {
        $this->db->execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllRead(int $userId): void
    {
        $this->db->execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ?",
            [$userId]
        );
    }
}

<?php
class NotificationController
{
    private NotificationModel $notifModel;
    public function __construct() { $this->notifModel = new NotificationModel(); }

    public function markRead(int $id): void
    {
        requireLogin();
        verifyCsrf();
        $this->notifModel->markRead($id, currentUser()['id']);
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
        redirect($ref);
    }

    public function markAllRead(): void
    {
        requireLogin();
        verifyCsrf();
        $this->notifModel->markAllRead(currentUser()['id']);
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
        redirect($ref);
    }
}

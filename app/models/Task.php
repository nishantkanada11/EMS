<?php
class Task
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get all tasks with assigned user name
    public function all()
    {
        $sql = "SELECT t.*, u.name AS assigned_user
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    ORDER BY t.id DESC";

        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get tasks by assigned user

    public function findByUser(int $userId)
    {
        $sql = "SELECT t.*, u.name AS assigned_user
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    WHERE t.assigned_to = ?
                    ORDER BY t.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function find(int $id)
    {
        $sql = "SELECT t.*, u.name AS assigned_user
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    WHERE t.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function create(string $title, string $description, int $assigned_to, string $status, string $start_date, string $due_date): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO tasks (title, description, assigned_to, status, start_date, due_date)
                VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("ssisss", $title, $description, $assigned_to, $status, $start_date, $due_date);
        return $stmt->execute();
    }

    public function update(int $id, string $title, string $description, int $assigned_to, string $status, string $start_date, string $due_date): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE tasks
                SET title=?, description=?, assigned_to=?, status=?, start_date=?, due_date=?
                WHERE id=?"
        );

        $stmt->bind_param("ssisssi", $title, $description, $assigned_to, $status, $start_date, $due_date, $id);
        return $stmt->execute();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->conn->prepare("UPDATE tasks SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getTaskCount(): int
    {
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM tasks");
        $row = $res ? $res->fetch_assoc() : null;
        return $row['total'] ?? 0;
    }


    public function getTaskStatusCounts(): array
    {
        $sql = "SELECT status, COUNT(*) AS count FROM tasks GROUP BY status";
        $res = $this->conn->query($sql);

        $statusCounts = [
            'Pending' => 0,
            'Ongoing' => 0,
            'Completed' => 0
        ];

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $status = ucfirst(strtolower($row['status']));
                if (isset($statusCounts[$status])) {
                    $statusCounts[$status] = (int) $row['count'];
                }
            }
        }

        return $statusCounts;
    }
}

<?php
class Task
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getTasks(?int $id = null, ?int $userId = null, string $sort = 'id', string $order = 'DESC'): array
    {
        // Whitelist columns and order
        $allowedSort = ['id', 'title', 'description', 'assigned_user', 'status', 'start_date', 'due_date'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSort))
            $sort = 'id';
        if (!in_array(strtoupper($order), $allowedOrder))
            $order = 'ASC';

        try {
            $sql = "SELECT t.*, u.name AS assigned_user
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id";

            $params = [];
            $types = '';

            if ($id !== null) {
                $sql .= " WHERE t.id = ?";
                $params[] = $id;
                $types .= 'i';
            } elseif ($userId !== null) {
                $sql .= " WHERE t.assigned_to = ?";
                $params[] = $userId;
                $types .= 'i';
            }

            $sql .= " ORDER BY $sort $order";

            if (!empty($params)) {
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $res = $stmt->get_result();
                return ($id !== null) ? [$res->fetch_assoc()] : $res->fetch_all(MYSQLI_ASSOC);
            } else {
                $res = $this->conn->query($sql);
                return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            }
        } catch (Exception $e) {
            error_log("Task::getTasks error: " . $e->getMessage());
            return [];
        }
    }

    public function create(string $title, string $description, int $assigned_to, string $status, string $start_date, string $due_date): bool
    {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO tasks (title, description, assigned_to, status, start_date, due_date)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt)
                throw new Exception("Prepare failed: " . $this->conn->error);

            $stmt->bind_param("ssisss", $title, $description, $assigned_to, $status, $start_date, $due_date);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Task::create error: " . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, string $title, string $description, int $assigned_to, string $status, string $start_date, string $due_date): bool
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE tasks
                 SET title=?, description=?, assigned_to=?, status=?, start_date=?, due_date=?
                 WHERE id=?"
            );

            if (!$stmt)
                throw new Exception("Prepare failed: " . $this->conn->error);

            $stmt->bind_param("ssisssi", $title, $description, $assigned_to, $status, $start_date, $due_date, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Task::update error: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE tasks SET status=? WHERE id=?");
            if (!$stmt)
                throw new Exception("Prepare failed: " . $this->conn->error);

            $stmt->bind_param("si", $status, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Task::updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id=?");
            if (!$stmt)
                throw new Exception("Prepare failed: " . $this->conn->error);

            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Task::delete error: " . $e->getMessage());
            return false;
        }
    }

    public function getTaskCount(): int
    {
        try {
            $res = $this->conn->query("SELECT COUNT(*) AS total FROM tasks");
            $row = $res ? $res->fetch_assoc() : null;
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Task::getTaskCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function getTaskStatusCounts(): array
    {
        $statusCounts = ['Pending' => 0, 'Ongoing' => 0, 'Completed' => 0];

        try {
            $sql = "SELECT status, COUNT(*) AS count FROM tasks GROUP BY status";
            $res = $this->conn->query($sql);

            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $status = ucfirst(strtolower($row['status']));
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status] = (int) $row['count'];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Task::getTaskStatusCounts error: " . $e->getMessage());
        }

        return $statusCounts;
    }
}
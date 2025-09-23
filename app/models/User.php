<?php
class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function all($excludeId = null, $sort = 'id', $order = 'ASC')
    {
        $allowedSort = ['id', 'name', 'email', 'mobile', 'role', 'department'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSort)) {
            $sort = 'id';
        }
        if (!in_array($order, $allowedOrder)) {
            $order = 'ASC';
        }
        if ($excludeId) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id != ? ORDER BY $sort $order");
            $stmt->bind_param("i", $excludeId);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query("SELECT * FROM users ORDER BY $sort $order");
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    public function find(int $id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Find useremail (for login)
    public function findByEmail(string $email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", trim($email));
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create(string $name, string $email, string $mobile, string $password, string $role, string $department)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE TRIM(email)=? OR TRIM(mobile)=?");
        $stmt->bind_param("ss", $email, $mobile);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0)
            return "exists";

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, email, mobile, password, role, department) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $name, $email, $mobile, $hashed, $role, $department);
        return $stmt->execute();
    }

    public function update(int $id, string $name, string $email, string $mobile, string $role, string $department)
    {
        $stmt = $this->conn->prepare(
            "SELECT id FROM users WHERE (TRIM(email)=? OR TRIM(mobile)=?) AND id != ?"
        );
        $stmt->bind_param("ssi", $email, $mobile, $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0)
            return "exists";

        $stmt = $this->conn->prepare(
            "UPDATE users SET name=?, email=?, mobile=?, role=?, department=? WHERE id=?"
        );
        $stmt->bind_param("sssssi", $name, $email, $mobile, $role, $department, $id);
        return $stmt->execute();
    }

    public function updatePassword(int $id, string $password)
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $id);
        return $stmt->execute();
    }

    public function changeRole(int $id, string $role)
    {
        $stmt = $this->conn->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->bind_param("si", $role, $id);
        return $stmt->execute();
    }

    public function delete(int $id)
    {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getEmployeeCount()
    {
        $res = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE role IN ('employee','tl')");
        $row = $res->fetch_assoc();
        return $row['total'] ?? 0;
    }

    public function getAllEmployees()
    {
        $sql = "SELECT u.*, t.name AS tl_name 
                FROM users u 
                LEFT JOIN users t ON u.tl_id = t.id
                WHERE u.role='employee'";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Assign employee to TL
    public function assignToTL(int $employeeId, int $tlId)
    {
        $stmt = $this->conn->prepare("UPDATE users SET tl_id=? WHERE id=? AND role='employee'");
        $stmt->bind_param("ii", $tlId, $employeeId);
        return $stmt->execute();
    }

    // Teams overview
    public function getTeamsOverview()
    {
        $sql = "SELECT tl.id AS tl_id, tl.name AS team_leader,
                       GROUP_CONCAT(emp.name SEPARATOR ', ') AS team_members,
                       COUNT(emp.id) AS total_members
                FROM users tl
                LEFT JOIN users emp ON emp.tl_id = tl.id AND emp.role='employee'
                WHERE tl.role='tl'
                GROUP BY tl.id, tl.name
                ORDER BY tl.name ASC";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Employees by TL
    public function getEmployeesByTL(int $tlId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role='employee' AND tl_id=?");
        $stmt->bind_param("i", $tlId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

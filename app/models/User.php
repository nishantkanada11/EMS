<?php
class User
{
private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function all($excludeId = null, $sort = 'id', $order = 'ASC'): array
    {
        $allowedSort = ['id', 'name', 'email', 'mobile', 'role', 'department'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sort, $allowedSort)) $sort = 'id';
        if (!in_array($order, $allowedOrder)) $order = 'ASC';

        try {
            if ($excludeId) {
                $stmt = $this->conn->prepare("SELECT * FROM users WHERE id != ? ORDER BY $sort $order");
                $stmt->bind_param("i", $excludeId);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $this->conn->query("SELECT * FROM users ORDER BY $sort $order");
            }

            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("User::all error: " . $e->getMessage());
            return [];
        }
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_assoc() : null;
        } catch (Exception $e) {
            error_log("User::find error: " . $e->getMessage());
            return null;
        }
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", trim($email));
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_assoc() : null;
        } catch (Exception $e) {
            error_log("User::findByEmail error: " . $e->getMessage());
            return null;
        }
    }

    public function create(string $name, string $email, string $mobile, string $password, string $role, string $department, ?string $profilePicture = null)
    {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=? OR mobile=?");
            $stmt->bind_param("ss", $email, $mobile);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) return "exists";

            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->conn->prepare(
                "INSERT INTO users (name, email, mobile, password, role, department, profile_image) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssssss", $name, $email, $mobile, $hashed, $role, $department, $profilePicture);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::create error: " . $e->getMessage());
            return false;
        }
    }

    //tl create request
    public function createEmployeeRequest(string $name, string $email, string $mobile, string $password, string $department, ?string $profilePicture, int $tlId)
    {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM employee_requests WHERE email=? OR mobile=?");
            $stmt->bind_param("ss", $email, $mobile);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) return "exists";

            $hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->conn->prepare(
                "INSERT INTO employee_requests (name, email, mobile, password, department, profile_image, tl_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssi", $name, $email, $mobile, $hashed, $department, $profilePicture, $tlId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::createEmployeeRequest error: " . $e->getMessage());
            return false;
        }
    }

    //pending request
    public function getEmployeeRequests(): array
    {
        try {
            $sql = "SELECT r.*, u.name AS requested_by_name 
                    FROM employee_requests r
                    JOIN users u ON r.tl_id = u.id
                    WHERE r.status = 'pending'
                    ORDER BY r.created_at DESC";
            $res = $this->conn->query($sql);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("User::getEmployeeRequests error: " . $e->getMessage());
            return [];
        }
    }

    //accept request and move to user
    public function approveEmployeeRequest(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM employee_requests WHERE id=? AND status='pending'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $request = $res ? $res->fetch_assoc() : null;
            if (!$request) return false;

            $stmt = $this->conn->prepare(
                "INSERT INTO users (name, email, mobile, password, role, department, profile_image) 
                 VALUES (?, ?, ?, ?, 'employee', ?, ?)"
            );
            $stmt->bind_param("ssssss", $request['name'], $request['email'], $request['mobile'], $request['password'], $request['department'], $request['profile_image']);
            $stmt->execute();
            
            $stmt = $this->conn->prepare("UPDATE employee_requests SET status='approved' WHERE id=?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::approveEmployeeRequest error: " . $e->getMessage());
            return false;
        }
    }

    public function rejectEmployeeRequest(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE employee_requests SET status='rejected' WHERE id=?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::rejectEmployeeRequest error: " . $e->getMessage());
            return false;
        }
    }

    public function update(
        int $id,
        string $name,
        string $email,
        string $mobile,
        string $role,
        string $department,
        ?string $profilePicture = null
    ) {
        try {
            //same mail
            $stmt = $this->conn->prepare(
                "SELECT id FROM users WHERE (TRIM(email) = ? OR TRIM(mobile) = ?) AND id != ?"
            );
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            $stmt->bind_param("ssi", $email, $mobile, $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                return "exists";
            }

            //pdate with-without profile image
            if ($profilePicture) {
                $stmt = $this->conn->prepare(
                    "UPDATE users 
                 SET name = ?, email = ?, mobile = ?, role = ?, department = ?, profile_image = ? 
                 WHERE id = ?"
                );
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->conn->error);
                }
                $stmt->bind_param("ssssssi", $name, $email, $mobile, $role, $department, $profilePicture, $id);
            } else {
                $stmt = $this->conn->prepare(
                    "UPDATE users 
                 SET name = ?, email = ?, mobile = ?, role = ?, department = ? 
                 WHERE id = ?"
                );
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->conn->error);
                }
                $stmt->bind_param("sssssi", $name, $email, $mobile, $role, $department, $id);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::update error: " . $e->getMessage());
            return false;
        }
    }


    public function updatePassword(int $id, string $password): bool
    {
        try {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::updatePassword error: " . $e->getMessage());
            return false;
        }
    }

    public function changeRole(int $id, string $role): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET role=? WHERE id=?");
            $stmt->bind_param("si", $role, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::changeRole error: " . $e->getMessage());
            return false;
        }
    }

    public function promote(int $id): bool
    {
        return $this->changeRole($id, 'tl');
    }
    
    public function demote(int $id): bool
    {
        return $this->changeRole($id, 'employee');
    }


    public function delete(int $id): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::delete error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeCount(): int
    {
        try {
            $res = $this->conn->query("SELECT COUNT(*) AS total FROM users WHERE role IN ('employee','tl','admin')");
            $row = $res ? $res->fetch_assoc() : null;
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("User::getEmployeeCount error: " . $e->getMessage());
            return 0;
        }
    }

    public function getAllEmployees(): array
    {
        try {
            $sql = "SELECT u.*, t.name AS tl_name 
                    FROM users u 
                    LEFT JOIN users t ON u.tl_id = t.id
                    WHERE u.role='employee'";
            $res = $this->conn->query($sql);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("User::getAllEmployees error: " . $e->getMessage());
            return [];
        }
    }

    public function assignToTL(int $employeeId, int $tlId): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET tl_id=? WHERE id=? AND role='employee'");
            $stmt->bind_param("ii", $tlId, $employeeId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::assignToTL error: " . $e->getMessage());
            return false;
        }
    }

    public function getTeamsOverview(): array
    {
        try {
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
        } catch (Exception $e) {
            error_log("User::getTeamsOverview error: " . $e->getMessage());
            return [];
        }
    }

    public function getEmployeesByTL(int $tlId): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE role='employee' AND tl_id=?");
            $stmt->bind_param("i", $tlId);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("User::getEmployeesByTL error: " . $e->getMessage());
            return [];
        }
    }

    public function getAssignableUsers(): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT id, name, email FROM users WHERE role IN ('employee','tl')");
            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("User::getAssignableUsers error: " . $e->getMessage());
            return [];
        }
    }
}
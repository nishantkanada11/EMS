<?php
class User
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function all($excludeId = null, $sort = 'id', $order = 'ASC', $id = null, $email = null): array
    {
        $allowedSort = ['id', 'name', 'email', 'mobile', 'role', 'department'];
        $allowedOrder = ['ASC', 'DESC'];

        $sort = in_array($sort, $allowedSort) ? $sort : 'id';
        $order = in_array(strtoupper($order), $allowedOrder) ? strtoupper($order) : 'ASC';

        //If $id or $email is passed → findBy()
        if ($id !== null || $email !== null) {
            $column = $id !== null ? 'id' : 'email';
            $value = $id ?? $email;
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE $column = ?");
            $stmt->bind_param($id !== null ? "i" : "s", $value);
            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }

        //Otherwise → get all users
        $query = "SELECT * FROM users";
        $params = [];

        if ($excludeId !== null) {
            $query .= " WHERE id != ?";
        }

        $query .= " ORDER BY $sort $order";
        $stmt = $this->conn->prepare($query);
        if ($excludeId !== null) {
            $stmt->bind_param("i", $excludeId);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
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

    public function createUserOrRequest(string $name, string $email, string $mobile, string $password, string $department, ?string $profilePicture = null, ?string $role = null, ?int $tlId = null, bool $isRequest = false)
    {
        try {
            // Duplicate check
            if ($isRequest) {
                // Check both users and requests
                $stmt = $this->conn->prepare("
                SELECT id FROM users WHERE email=? OR mobile=? 
                UNION 
                SELECT id FROM employee_requests WHERE email=? OR mobile=?
            ");
                $stmt->bind_param("ssss", $email, $mobile, $email, $mobile);
            } else {
                // Check only users
                $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=? OR mobile=?");
                $stmt->bind_param("ss", $email, $mobile);
            }

            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                return "exists";
            }
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            if ($isRequest) {
                $stmt = $this->conn->prepare("
                INSERT INTO employee_requests (name, email, mobile, password, department, profile_image, tl_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
                $stmt->bind_param("ssssssi", $name, $email, $mobile, $hashed, $department, $profilePicture, $tlId);
            } else {
                $stmt = $this->conn->prepare("
                INSERT INTO users (name, email, mobile, password, role, department, profile_image)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
                $stmt->bind_param("sssssss", $name, $email, $mobile, $hashed, $role, $department, $profilePicture);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::createUserOrRequest error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeRequests(): array
    {
        try {
            $sql = "SELECT r.*, u.name AS requested_by_name
            FROM employee_requests r
            LEFT JOIN users u ON r.tl_id = u.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC";
            $res = $this->conn->query($sql);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("User::getEmployeeRequests error: " . $e->getMessage());
            return [];
        }
    }

    public function processEmployeeRequest(int $id, string $action): bool
    {
        try {
            if ($action === 'approve') {
                $stmt = $this->conn->prepare("SELECT * FROM employee_requests WHERE id=? AND status='pending'");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                $request = $res ? $res->fetch_assoc() : null;

                if (!$request)
                    return false;

                $stmt = $this->conn->prepare(
                    "INSERT INTO users (name, email, mobile, password, role, department, profile_image)
                 VALUES (?, ?, ?, ?, 'employee', ?, ?)"
                );
                $stmt->bind_param("ssssss", $request['name'], $request['email'], $request['mobile'], $request['password'], $request['department'], $request['profile_image']);
                $stmt->execute();

                $status = 'approved';
            } elseif ($action === 'reject') {
                $status = 'rejected';
            } else {
                return false;
            }

            $stmt = $this->conn->prepare("UPDATE employee_requests SET status=? WHERE id=?");
            $stmt->bind_param("si", $status, $id);
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("User::processEmployeeRequest error: " . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, string $name, string $email, string $mobile, string $role, string $department, ?string $profilePicture = null)
    {
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

    public function getUsers(array $filters = []): array
    {
        try {
            $sql = "SELECT u.*, t.name AS tl_name 
                FROM users u 
                LEFT JOIN users t ON u.tl_id = t.id 
                WHERE 1=1";

            $params = [];
            $types = "";

            if (!empty($filters['role'])) {
                $sql .= " AND u.role = ?";
                $types .= "s";
                $params[] = $filters['role'];
            }

            if (array_key_exists('tl_id', $filters)) {
                if ($filters['tl_id'] === null) {
                    $sql .= " AND u.tl_id IS NULL";
                } else {
                    $sql .= " AND u.tl_id = ?";
                    $types .= "i";
                    $params[] = $filters['tl_id'];
                }
            }

            if (!empty($filters['assignable'])) {
                $sql .= " AND u.role IN ('employee', 'tl')";
            }

            $sql .= " ORDER BY u.name ASC";
            $stmt = $this->conn->prepare($sql);

            if ($params) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $res = $stmt->get_result();
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("User::getUsers error: " . $e->getMessage());
            return [];
        }
    }

    public function getEmployeeCount(): int
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role IN ('employee', 'tl', 'admin')");
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            return $row['total'] ?? 0;
        } catch (Exception $e) {
            error_log("User::getEmployeeCount error: " . $e->getMessage());
            return 0;
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
}
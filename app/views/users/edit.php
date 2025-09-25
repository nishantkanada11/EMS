<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Edit User</h2>

<form action="index.php?controller=User&action=update" method="POST">
    <input type="hidden" name="id" value="<?= $user['id']; ?>">

    <label>Name:</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required><br><br>

    <label>Mobile:</label><br>
    <input type="text" name="mobile" value="<?= htmlspecialchars($user['mobile']); ?>" required><br><br>

    <label>Role:</label><br>
    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <select name="role" required>
            <option value="employee" <?= $user['role'] === 'employee' ? 'selected' : ''; ?>>Employee</option>
            <option value="tl" <?= $user['role'] === 'tl' ? 'selected' : ''; ?>>Team Leader</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>
    <?php else: ?>
        <input type="text" name="role" value="<?= $user['role']; ?>" readonly>
    <?php endif; ?>
    <br><br>

    <label>Department:</label><br>
    <input type="text" name="department" value="<?= htmlspecialchars($user['department']); ?>"><br><br>

    <label>New Password (leave blank to keep current):</label><br>
    <input type="text" name="password"><br><br>

    <button type="submit">Update User</button>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Edit User</h2>

<?php $old = $_SESSION['old'] ?? [];
unset($_SESSION['old']); ?>

<form action="index.php?controller=User&action=update" method="POST" enctype="multipart/form-data">

       <input type="hidden" name="id" value="<?= $user['id']; ?>">

       <label>Name:</label><br>
       <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? $user['name']); ?>"><br><br>

       <label>Email:</label><br>
       <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? $user['email']); ?>"><br><br>

       <label>Mobile:</label><br>
       <input type="text" name="mobile" value="<?= htmlspecialchars($old['mobile'] ?? $user['mobile']); ?>"><br><br>

       <label>Role:</label><br>
       <?php if ($_SESSION['user']['role'] === 'admin'): ?>
              <?php $roleValue = $old['role'] ?? $user['role']; ?>
              <select name="role">
                     <option value="employee" <?= $roleValue === 'employee' ? 'selected' : ''; ?>>Employee</option>
                     <option value="tl" <?= $roleValue === 'tl' ? 'selected' : ''; ?>>Team Leader</option>
                     <option value="admin" <?= $roleValue === 'admin' ? 'selected' : ''; ?>>Admin</option>
              </select>
       <?php else: ?>
              <input type="text" name="role" value="<?= htmlspecialchars($user['role']); ?>" readonly>
       <?php endif; ?>
       <br><br>

       <label>Department:</label><br>
       <input type="text" name="department"
              value="<?= htmlspecialchars($old['department'] ?? $user['department']); ?>"><br><br>

       <label>New Password (leave blank to keep current):</label><br>
       <input type="text" name="password" value="<?= htmlspecialchars($old['password'] ?? ''); ?>"><br><br>

       <label>Profile Picture:</label><br>
       <img src="uploads/<?= htmlspecialchars($user['profile_image'] ?? 'default.png'); ?>" width="80" height="80"
              alt="Current Profile">
       <input type="file" name="profile_picture"><br><br>


       <button type="submit">Update User</button>
</form>


<?php include __DIR__ . '/../layouts/footer.php'; ?>
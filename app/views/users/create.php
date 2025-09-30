<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="table-header">
    <a href="index.php?controller=User&action=index">Back to Users</a>
</div>

<h2>Create New User</h2>

<!-- Show flash messages -->
<?php if (!empty($_SESSION['flash']['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['flash']['error'];
    unset($_SESSION['flash']['error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash']['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash']['success'];
    unset($_SESSION['flash']['success']); ?></div>
<?php endif; ?>

<form method="POST" action="index.php?controller=User&action=store" enctype="multipart/form-data">

    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Mobile</label>
        <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($old['mobile'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="text" name="password" class="form-control">
    </div>

    <div class="form-group">
        <label>Department</label>
        <input type="text" name="department" class="form-control"
            value="<?= htmlspecialchars($old['department'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Job Title</label><br>
        <?php $roleOld = $old['role'] ?? 'employee'; ?>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <input type="radio" name="role" value="employee" <?= $roleOld === 'employee' ? 'checked' : '' ?>> Employee
            <input type="radio" name="role" value="tl" <?= $roleOld === 'tl' ? 'checked' : '' ?>> Team Leader
            <input type="radio" name="role" value="admin" <?= $roleOld === 'admin' ? 'checked' : '' ?>> Admin
        <?php else: ?>
            <input type="radio" name="role" value="employee" checked> Employee
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Profile Picture</label>
        <input type="file" name="profile_picture" class="form-control">
    </div>


    <button type="submit" class="btn btn-primary">Add</button>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="table-header">
    <a href="index.php?controller=User&action=index">Back to Users</a>
</div>

<h2>Create New User</h2>

<form method="POST" action="index.php?controller=User&action=store">
    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" class="form-control">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
    </div>

    <div class="form-group">
        <label>Mobile</label>
        <input type="text" name="mobile" class="form-control">
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="text" name="password" class="form-control">
    </div>

    <div class="form-group">
        <label>Department</label>
        <input type="text" name="department" class="form-control">
    </div>

    <div class="form-group">
        <label>Job Title</label><br>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <input type="radio" name="role" value="employee" checked> Employee
            <input type="radio" name="role" value="tl"> Team Leader
            <input type="radio" name="role" value="admin"> Admin
        <?php else: ?>
            <input type="radio" name="role" value="employee" checked> Employee
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Add</button>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
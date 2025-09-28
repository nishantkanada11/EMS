<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="table-header">
    <a href="index.php?controller=Task&action=index">Back to Tasks</a>
</div>

<h2>Create New Task</h2>

<?php if (!empty($_SESSION['flash']['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['flash']['error'];
    unset($_SESSION['flash']['error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash']['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash']['success'];
    unset($_SESSION['flash']['success']); ?></div>
<?php endif; ?>

<?php
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']); // Clear old input after using
?>

<form method="POST" action="index.php?controller=Task&action=store" onsubmit="return validateDates()">
    <label>Task Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>"><br><br>

    <label>Description:</label><br>
    <textarea name="description"><?= htmlspecialchars($old['description'] ?? '') ?></textarea><br><br>

    <label for="assigned_to">Assign To:</label>
    <select name="assigned_to" id="assigned_to">
        <option value="">-- Select Employee --</option>
        <?php foreach ($employees as $employee): ?>
            <option value="<?= $employee['id']; ?>" <?= isset($old['assigned_to']) && $old['assigned_to'] == $employee['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($employee['name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Start Date:</label><br>
    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($old['start_date'] ?? '') ?>"
        min="<?= date('Y-m-d') ?>">

    <label>Due Date:</label><br>
    <input type="date" id="due_date" name="due_date" value="<?= htmlspecialchars($old['due_date'] ?? '') ?>"
        min="<?= date('Y-m-d') ?>"><br><br>

    <button type="submit">Create Task</button>
</form>

<script>
    function validateDates() {
        let today = new Date().toISOString().split("T")[0];
        let startDate = document.getElementById("start_date").value;
        let dueDate = document.getElementById("due_date").value;

        if (startDate < today) {
            alert("Start date cannot be before today!");
            return false;
        }

        if (dueDate < startDate) {
            alert("Due date cannot be before start date!");
            return false;
        }

        return true;
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
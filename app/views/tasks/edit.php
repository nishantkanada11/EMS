<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="table-header">
    <a href="index.php?controller=Task&action=index">Back to Tasks</a>
</div>

<h2>Edit Task</h2>

<?php $old = $_SESSION['old'] ?? [];
unset($_SESSION['old']); ?>

<form method="POST" action="index.php?controller=Task&action=update" onsubmit="return validateDates()">
    <input type="hidden" name="id" value="<?= $task['id']; ?>">

    <label>Task Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($old['title'] ?? $task['title']); ?>"><br><br>

    <label>Description:</label><br>
    <textarea
        name="description"><?= htmlspecialchars($old['description'] ?? $task['description']); ?></textarea><br><br>

    <label>Assign To (Employee):</label><br>
    <select name="assigned_to">
        <option value="">-- Select Employee --</option>
        <?php foreach ($employees as $emp): ?>
            <option value="<?= $emp['id']; ?>" <?= (isset($old['assigned_to']) ? $old['assigned_to'] : $task['assigned_to']) == $emp['id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($emp['name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Status:</label><br>
    <select name="status">
        <?php $statusValue = $old['status'] ?? $task['status']; ?>
        <option value="pending" <?= $statusValue === 'pending' ? 'selected' : ''; ?>>Pending</option>
        <option value="ongoing" <?= $statusValue === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
        <option value="completed" <?= $statusValue === 'completed' ? 'selected' : ''; ?>>Completed</option>
    </select><br><br>

    <label>Start Date:</label><br>
    <input type="date" id="start_date" name="start_date"
        value="<?= htmlspecialchars($old['start_date'] ?? $task['start_date']); ?>"><br><br>

    <label>Due Date:</label><br>
    <input type="date" id="due_date" name="due_date"
        value="<?= htmlspecialchars($old['due_date'] ?? $task['due_date']); ?>"><br><br>

    <button type="submit">Update Task</button>
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
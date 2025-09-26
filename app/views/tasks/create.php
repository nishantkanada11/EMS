<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="table-header">
    <a href="index.php?controller=Task&action=index">Back to Tasks</a>
</div>

<h2>Create New Task</h2>

<form method="POST" action="index.php?controller=Task&action=store" onsubmit="return validateDates()">
    <label>Task Title:</label><br>
    <input type="text" name="title"><br><br>

    <label>Description:</label><br>
    <textarea name="description"></textarea><br><br>

    <label for="assigned_to">Assign To:</label>
    <select name="assigned_to" id="assigned_to">
        <option value="">-- Select Employee --</option>
        <?php foreach ($employees as $employee): ?>
            <option value="<?= $employee['id']; ?>"><?= htmlspecialchars($employee['name']); ?></option>
        <?php endforeach; ?>
    </select>


    <label>Start Date:</label><br>
    <input type="date" id="start_date" name="start_date" min="<?= date('Y-m-d') ?>">

    <label>Due Date:</label><br>
    <input type="date" id="due_date" name="due_date" min="<?= date('Y-m-d') ?>">

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
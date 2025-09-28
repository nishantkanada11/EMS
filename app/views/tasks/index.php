<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Tasks List</h2>

<div class="table-header">
    <?php if ($_SESSION['user']['role'] !== 'employee'): ?>
        <a href="index.php?controller=Task&action=create">+ Create Task</a>
    <?php endif; ?>
</div>

<div class="table-wrapper">
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <?php
                $columns = [
                    'id' => 'ID',
                    'title' => 'Title',
                    'description' => 'Description',
                    'assigned_user' => 'Assigned To',
                    'status' => 'Status',
                    'start_date' => 'Start Date',
                    'due_date' => 'Due Date'
                ];
                foreach ($columns as $col => $label):
                    ?>
                    <th>
                        <a
                            href="index.php?controller=Task&action=index&sort=<?= $col ?>&order=<?= ($col === $sort) ? ($order === 'ASC' ? 'DESC' : 'ASC') : 'ASC' ?>">
                            <?= $label ?>
                        </a>
                    </th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['id']); ?></td>
                    <td><?= htmlspecialchars($task['title']); ?></td>
                    <td><?= htmlspecialchars($task['description']); ?></td>
                    <td><?= htmlspecialchars($task['assigned_user']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($task['status'])); ?></td>
                    <td><?= htmlspecialchars($task['start_date']); ?></td>
                    <td><?= htmlspecialchars($task['due_date']); ?></td>
                    <td>
                        <?php if ($_SESSION['user']['role'] !== 'employee'): ?>
                            <a href="index.php?controller=Task&action=edit&id=<?= urlencode($task['id']); ?>">Edit</a> |
                            <a href="index.php?controller=Task&action=delete&id=<?= urlencode($task['id']); ?>"
                                onclick="return confirm('Are you sure?')">Delete</a>
                        <?php else: ?>
                            <form method="POST" action="index.php?controller=Task&action=updateStatus" style="display:inline;">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($task['id']); ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="ongoing" <?= $task['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed
                                    </option>
                                </select>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
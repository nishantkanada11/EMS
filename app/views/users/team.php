<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>My Team - Add Employees</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Team Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($employees as $emp): ?>
        <tr>
            <td><?= $emp['id'] ?></td>
            <td><?= htmlspecialchars($emp['name']) ?></td>
            <td><?= htmlspecialchars($emp['email']) ?></td>
            <td><?= htmlspecialchars($emp['department']) ?></td>
            <td>
                <?php if ($emp['tl_id']): ?>
                    Assigned to TL: <?= htmlspecialchars($emp['tl_name']) ?>
                <?php else: ?>
                    Not Assigned
                <?php endif; ?>
            </td>
            <td>
                <?php if (empty($emp['tl_id'])): ?>
                    <a href="index.php?controller=User&action=addToTeam&id=<?= $emp['id'] ?>">Add to Team</a>
                <?php else: ?>
                    Already in a Team
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
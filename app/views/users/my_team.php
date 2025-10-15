<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>My Team Members</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Role</th>
    </tr>
    <?php if (!empty($teamMembers)): ?>
        <?php foreach ($teamMembers as $emp): ?>
            <tr>
                <td><?= $emp['id'] ?></td>
                <td><?= htmlspecialchars($emp['name']) ?></td>
                <td><?= htmlspecialchars($emp['email']) ?></td>
                <td><?= htmlspecialchars($emp['department']) ?></td>
                <td><?= htmlspecialchars($emp['role']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">No team members assigned yet.</td>
        </tr>
    <?php endif; ?>
</table>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Teams Overview</h2>
<table border="1" width="100%" cellpadding="8" cellspacing="0">
    <thead style="background:#23395d; color:#fff;">
        <tr>
            <th>Team Leader</th>
            <th>Team Members</th>
            <th>Total Members</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($teams as $team): ?>
            <tr>
                <td><?= htmlspecialchars($team['team_leader']) ?></td>
                <td>
                    <?= $team['team_members'] ? htmlspecialchars($team['team_members']) : "<i>No team members yet</i>" ?>
                </td>
                <td><?= $team['total_members'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
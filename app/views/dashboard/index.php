<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-summary">
    <h2>Dashboard Summary</h2>

    <div style="display: flex; gap: 20px; margin-bottom: 20px;">
        <div style="flex: 1; text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
            <h3>Total Employees</h3>
            <p style="font-size: 24px; font-weight: bold;"><?= $totalEmployees ?></p>
        </div>
        <div style="flex: 1; text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px;">
            <h3>Total Tasks</h3>
            <p style="font-size: 24px; font-weight: bold;"><?= $totalTasks ?></p>
        </div>
    </div>

    <h3>Task Status Breakdown</h3>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: left;">
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Pending</td>
                <td><?= $taskStatusCounts['Pending'] ?></td>
            </tr>
            <tr>
                <td>Ongoing</td>
                <td><?= $taskStatusCounts['Ongoing'] ?></td>
            </tr>
            <tr>
                <td>Completed</td>
                <td><?= $taskStatusCounts['Completed'] ?></td>
            </tr>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
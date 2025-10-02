<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Pending Employee Requests</h2>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash <?= $_SESSION['flash']['type']; ?>">
        <?= $_SESSION['flash']['message']; ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (empty($requests)): ?>
    <p>No pending requests.</p>
<?php else: ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Department</th>
            <th>Profile Image</th>
            <th>Requested By (TL)</th>
            <th>Requested At</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td><?= htmlspecialchars($req['id']); ?></td>
                <td><?= htmlspecialchars($req['name']); ?></td>
                <td><?= htmlspecialchars($req['email']); ?></td>
                <td><?= htmlspecialchars($req['mobile']); ?></td>
                <td><?= htmlspecialchars($req['department']); ?></td>
                <td>
                    <?php if (!empty($req['profile_image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($req['profile_image']); ?>" alt="Profile" width="50" height="50">
                    <?php else: ?>
                        <img src="uploads/default.png" alt="Profile" width="50" height="50">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($req['requested_by_name']); ?></td>
                <td><?= htmlspecialchars($req['created_at']); ?></td>
                <td>
                    <a href="index.php?controller=User&action=approveRequest&id=<?= $req['id']; ?>" 
                       onclick="return confirm('Approve this request?');">Approve</a> |
                    <a href="index.php?controller=User&action=rejectRequest&id=<?= $req['id']; ?>" 
                       onclick="return confirm('Reject this request?');">Reject</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

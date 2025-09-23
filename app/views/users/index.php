<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Users List</h2>
<div class="table-header">
    <a href="index.php?controller=User&action=create">+ Create User</a>
</div>

<div class="table-wrapper">
    <table border="1" cellpadding="10">
        <tr>
            <th><a href="index.php?controller=User&action=index&sort=id&order=<?= $nextOrder ?>">ID</a></th>
            <th><a href="index.php?controller=User&action=index&sort=name&order=<?= $nextOrder ?>">Name</a></th>
            <th><a href="index.php?controller=User&action=index&sort=email&order=<?= $nextOrder ?>">Email</a></th>
            <th><a href="index.php?controller=User&action=index&sort=mobile&order=<?= $nextOrder ?>">Mobile</a></th>
            <th><a href="index.php?controller=User&action=index&sort=role&order=<?= $nextOrder ?>">Role</a></th>
            <th><a href="index.php?controller=User&action=index&sort=department&order=<?= $nextOrder ?>">Department</a>
            </th>
            <th>Actions</th>
        </tr>

        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id']; ?></td>
                <td><?= $user['name']; ?></td>
                <td><?= $user['email']; ?></td>
                <td><?= $user['mobile']; ?></td>
                <td><?= ucfirst($user['role']); ?></td>
                <td><?= $user['department']; ?></td>
                <td>
                    <a href="index.php?controller=User&action=edit&id=<?= $user['id']; ?>">Edit</a> |
                    <a href="index.php?controller=User&action=delete&id=<?= $user['id']; ?>"
                        onclick="return confirm('Are you sure?')">Delete</a> |
                    <?php if ($user['role'] === 'employee'): ?>
                        <a href="index.php?controller=User&action=promote&id=<?= $user['id']; ?>">Promote to TL</a>
                    <?php elseif ($user['role'] === 'tl'): ?>
                        <a href="index.php?controller=User&action=demote&id=<?= $user['id']; ?>">Demote to Employee</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
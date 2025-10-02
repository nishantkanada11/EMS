<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../helpers/flash.php';

$flashMessages = getFlashMessages();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Management System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <?php
    if (!empty($flashMessages)) {
        foreach ($flashMessages as $type => $messages) {
            foreach ($messages as $msg) {
                $class = "flash-" . htmlspecialchars($type);
                echo "<div class='flash-message {$class}'>" . htmlspecialchars($msg) . "</div>";
            }
        }
    }
    ?>

    <nav style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <?php if (!empty($_SESSION['user'])): ?>
                <?php $role = $_SESSION['user']['role'] ?? null; ?>

                <?php if ($role === 'admin'): ?>
                    <a href="index.php?controller=User&action=index">Users</a>
                    <a href="index.php?controller=Task&action=index">Tasks</a>
                    <a href="index.php?controller=Dashboard&action=index">Dashboard</a>
                    <a href="index.php?controller=User&action=teamsOverview">View Team</a>
                    <a href="index.php?controller=User&action=requests">Requests</a>
                    <a href="index.php?controller=User&action=edit&id=<?= $_SESSION['user']['id']; ?>">Edit Profile</a>

                <?php elseif ($role === 'tl'): ?>
                    <a href="index.php?controller=Task&action=index">Tasks</a>
                    <a href="index.php?controller=User&action=create">Create User</a>
                    <a href="index.php?controller=User&action=team">Add Members</a>
                    <a href="index.php?controller=User&action=myTeam">My Team</a>
                    <a href="index.php?controller=User&action=edit&id=<?= $_SESSION['user']['id']; ?>">Edit Profile</a>

                <?php elseif ($role === 'employee'): ?>
                    <a href="index.php?controller=Task&action=index">Tasks</a>
                    <a href="index.php?controller=User&action=edit&id=<?= $_SESSION['user']['id']; ?>">Edit Profile</a>
                <?php endif; ?>

                <a href="index.php?controller=Auth&action=logout">Logout</a>
            <?php endif; ?>
        </div>

        <div class="profile" style="display:flex; align-items:center; gap:10px;">
            <?php
            //Pick profile image from session
            $profileImage = !empty($_SESSION['user']['profile_image'])
                ? 'uploads/' . $_SESSION['user']['profile_image']
                : 'uploads/default.png';
            ?>

            <?php if (!empty($_SESSION['user']['name'])): ?>
                <span><?= htmlspecialchars($_SESSION['user']['name']); ?></span>
                <img src="<?= htmlspecialchars($profileImage); ?>" alt="Profile Picture"
                    style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #ddd;">
            <?php endif; ?>
        </div>

    </nav>
<?php
if (isset($_SESSION['user'])) {
    header("Location: index.php?controller=User&action=index");
    exit;
}
?>

<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="login-container">
    <div class="login-box">
        <h2>Login</h2>

        <form method="POST" action="index.php?controller=Auth&action=authenticate">
            <label>Email:</label><br>
            <input type="email" name="email" required><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br>

            <button type="submit">Login</button>
        </form>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
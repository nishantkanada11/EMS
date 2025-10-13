<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="register-container">
    <div class="register-box">
        <h2>Register New Employee</h2>

        <form method="POST" action="index.php?controller=Auth&action=registerRequest" enctype="multipart/form-data">
            <label>Name:</label><br>
            <input type="text" name="name" required><br>

            <label>Email:</label><br>
            <input type="email" name="email" required><br>

            <label>Mobile:</label><br>
            <input type="text" name="mobile" required><br>

            <label>Department:</label><br>
            <input type="text" name="department" required><br>

            <label>Password:</label><br>
            <input type="text" name="password" required><br>

            <label>Profile Image:</label><br>
            <input type="file" name="profile_image"><br><br>

            <button type="submit">Submit Request</button>
            <p>Already have an account? <a href="index.php">Login</a></p>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

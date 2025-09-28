<script>
    // Hide flash messages after 3 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.flash-message');
        messages.forEach(msg => {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500); // remove from DOM after fade
        });
    }, 3000); // 3000ms = 3s
</script>

</body>
</html>
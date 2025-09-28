<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
}

function getFlashMessages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']); // clear after reading
    return $messages;
}

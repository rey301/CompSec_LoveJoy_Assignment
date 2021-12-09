<?php
    // Generate a token
    $timestamp = time();
    if (empty($_SESSION['key'])) {
        $_SESSION['key'] = bin2hex(random_bytes(32));
    }

    $token = hash_hmac('sha256', $timestamp, $_SESSION['key']);
    if (isset($_SESSION['token'])) {
        unset($_SESSION['token']);
    }

    $_SESSION['token'] = $token;
?>
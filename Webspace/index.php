<?php 
	session_start();
	require "csrfToken.php";

	// Only generate once and store key permanently in session variable
	if (!isset($_SESSION['key'])) {
		$_SESSION['key'] = bin2hex(openssl_random_pseudo_bytes(256));
	}
	require __DIR__ . '/login/complexLoginForm.php';
?>
<?php 
session_start();

$errors = []; // for storing the error messages
$inputs = []; // for storing sanitized input values

$request_method = strtoupper($_SERVER['REQUEST_METHOD']);

if ($request_method === 'GET') {
	// generate a token
	$_SESSION['token'] = md5(uniqid(mt_rand(), true));
	// show the form
	require __DIR__ . '/login/complexLoginForm.php';
} elseif ($request_method === 'POST') {
	// handle the form submission
	require __DIR__ .  '/login/complexLoginCheck.php';
	// re-display the form if the form contains errors
	if ($errors) {
		require	__DIR__ .  '/login/complexLoginForm.php';
	}
}

?>
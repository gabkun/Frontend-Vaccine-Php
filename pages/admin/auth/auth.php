<?php
session_start();

// 🚫 Prevent browser from caching protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 🔑 Check if user has a token
if (!isset($_SESSION["token"])) {
    header("Location: /login");
    exit;
}

$token = $_SESSION["token"];
$url = "http://localhost:8080/auth/profile";

$options = [
    "http" => [
        "header"  => "Authorization: Bearer " . $token,
        "method"  => "GET"
    ]
];

$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);

// If API call failed
if ($result === FALSE) {
    session_destroy();
    header("Location: /login");
    exit;
}

$response = json_decode($result, true);

// If backend says token is invalid
if (isset($response["message"]) && 
   ($response["message"] === "No token provided" || 
    $response["message"] === "Invalid or expired token")) {
    session_destroy();
    header("Location: /login");
    exit;
}

<?php
session_start();
// Redirect removed so both guests and logged-in users can view the landing page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome - Basic Data Capturing App</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Welcome to the Basic Data Capturing App</h1>
    <p>Please login or register to continue.</p>
    <div style="margin-top:2em;">
        <a class="button" href="/basic_data_capturing_app/login.php">Login</a>
        <a class="button" href="/basic_data_capturing_app/register.php">Register</a>
    </div>
    <style>
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            background: #007bff;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .button:hover {
            background: #0056b3;
        }
    </style>
</body>
</html>

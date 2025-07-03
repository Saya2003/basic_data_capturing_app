<?php
session_start();
session_unset();
session_destroy();
header('Location: /basic_data_capturing_app/landing.php');
exit;

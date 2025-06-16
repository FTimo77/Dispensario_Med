<?php
session_start();
session_unset();
session_destroy();
header("Location: /dispensario_med/index.php");
exit;
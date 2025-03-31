<?php
session_start();

setcookie("userType", "", time() - 3600, "/");

session_unset();

session_destroy();

header("Location: ./");
?>

<!DOCTYPE html>
<html lang="en">
</html>

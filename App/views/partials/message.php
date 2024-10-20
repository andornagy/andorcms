<?php

use Framework\Session;

$sucessMessage = Session::getFlashMessage('success_message');
$errorMessage = Session::getFlashMessage('error_message');

if ($sucessMessage !== null) {
    echo "<div class='message bg-green-100 p-3 my-3'>{$sucessMessage}</div>";
};

if ($errorMessage !== null) {
    echo "<div class='message bg-red-100 p-3 my-3'>{$errorMessage}</div>";
};

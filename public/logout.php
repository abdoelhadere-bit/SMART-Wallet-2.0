<?php
require __DIR__ . '/../app/Core/Session.php';

Session::destroy();

header('Location: index.php');
exit;

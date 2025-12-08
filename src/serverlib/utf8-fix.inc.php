<?php
// UTF-8 Fix für b1gMail
header('Content-Type: text/html; charset=UTF-8');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
mysqli_set_charset(\->_connectionID, 'utf8mb4');

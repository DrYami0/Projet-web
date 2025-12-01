<?php
require_once "../../Controller/TypeC.php";

if (isset($_GET['wid'])) {
    $wid = intval($_GET['wid']);
    $dictC = new TypeC();
    $dictC->deleteType($wid);
    header('Location: DisplayTypes.php?deleted=1');
    exit;
} else {
    header('Location: DisplayTypes.php?error=1');
    exit;
}
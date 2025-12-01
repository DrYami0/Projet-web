<?php
require_once "../../Controller/DictionaryC.php";

if (isset($_GET['wid'])) {
    $wid = intval($_GET['wid']);
    $dictC = new DictionaryC();
    $dictC->deleteWord($wid);
    header('Location: DisplayWords.php?deleted=1');
    exit;
} else {
    header('Location: DisplayWords.php?error=1');
    exit;
}
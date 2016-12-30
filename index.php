<?php
include 'ErrorManager.class.php';
include 'Dumper.class.php';
include 'Codebox.class.php';

use PHPCodebox\Codebox;

set_time_limit(3);
// if AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") {
    $codebox = new Codebox(true);
    $codebox->parseCode($_POST['code']);
}

if (isset($_GET['fn']) && !empty($_GET['fn'])) {
    $fn = 'functions/' .$_GET['fn']. '.php';
    if (file_exists($fn)) {
        $fn = file_get_contents($fn);
    }
    else $fn = "";
} else $fn = "";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PHP Codebox: Test your PHP code</title>
    <meta name="author" content="Dennis Jungbauer">
    <meta name="editor" content="Sublime Text 3">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fira+Mono">
    <!-- Alternative: Patrick Hand SC -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Annie+Use+Your+Telescope">
</head>
<body>
    <div id="flex-container">
        <div class="button-area">
            <button class="btn-submit" title="Ctrl + Enter">Run</button>
        </div>
        <div class="row-wrapper">
            <div class="codebox-wrapper">
                <div class="line-numbers"></div>
                <textarea class="code" name="code" autofocus spellcheck="false"><?= $fn ?></textarea>
            </div>
            <iframe class="output" name="outputFrame" sandbox="allow-forms allow-scripts allow-modals allow-same-origin"></iframe>
        </div>
    </div>

    <script src="http://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="js/codebox.js"></script>
    <script src="js/autosize.js"></script>
</body>
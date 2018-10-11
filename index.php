<?php
include 'View.class.php';
include 'ErrorManager.class.php';
include 'Dumper.class.php';
include 'Timer.class.php';
include 'Codebox.class.php';

use PHPCodebox\Codebox;


$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax || isset($_POST['codebox-output-form'])) {
    $codebox = new Codebox(true);
    $output = $codebox->parseCode($_POST['codebox-code']);

    die($output);
}
else {
    $code = Codebox::getCodeTemplate();
    $output = null;

    if (isset($_POST['codebox-code'])) {
        $code = $_POST['codebox-code'];
        $codebox = new Codebox(true);
        $output = htmlspecialchars( $codebox->parseCode($code) );
    }
}
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
        <header class="button-area">
            <button class="btn-submit" title="Ctrl + Enter">Run</button>
        </header>
        <main class="flex">
            <div class="editor flex scrollable">
                <aside class="line-numbers"></aside>
                <textarea class="code" name="codebox-code" autofocus spellcheck="false"><?= htmlspecialchars($code) ?></textarea>
            </div>
            <iframe class="output flex" name="outputFrame" sandbox="allow-forms allow-scripts allow-modals allow-same-origin"
                srcdoc="<?= $output ?>">
            </iframe>
        </main>
    </div>

    <script src="http://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script>if(!window.jQuery) document.write('<script src="js/jquery-3.1.1.min.js"><\/script>')</script>
    <script src="js/codebox.js"></script>
    <script src="js/notification.js"></script>
</body>
</html>
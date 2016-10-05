<?php
if (!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
	if (!empty($code = $_POST["code"])) {
		// wenn HTML-Tag am Anfang und/oder wenn PHP-Starttag im Code vorhanden,
		// Code parsen, aber auch HTML erlauben
		if (preg_match("/^<[\w!]/", $code) || strpos($code, "<?php") === 0) {
			$code = "?>" . $code;
		}
		eval($code);
	}

	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>PHP Codebox: Test your PHP code</title>
	<meta name="author" content="Dennis Jungbauer">
	<meta name="editor" content="Sublime Text 3">
	<link rel="stylesheet" href="php-codebox.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Fira+Mono">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Patrick+Hand+SC|Annie+Use+Your+Telescope|Caveat+Brush">
</head>
<body>
	<div id="flex-container">
		<div class="button-area">
			<button class="btn-submit" title="Ctrl + Enter">Run</button>
		</div>
		<div class="row-wrapper">
			<textarea class="code" name="code" autofocus spellcheck="false"></textarea>
			<iframe class="output" sandbox="allow-forms allow-scripts allow-modals allow-same-origin"></iframe>
		</div>
	</div>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="/browserDetect.js"></script>
	<script src="php-codebox.js"></script>
</body>
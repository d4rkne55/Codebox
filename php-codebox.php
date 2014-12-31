<!DOCTYPE html>
<html>
<head>
<title>PHP-Codebox: Test your PHP code</title>
<meta charset="utf-8">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<style>
body { margin: 0; background-color: #EEE; }

div.blue {
	float: left;
	box-sizing: border-box;
	width: 50%;
	margin-top: 10%;
	padding: 1.5% 0;
	background: cornflowerblue;
	/*box-shadow: 0px 0px 3px 2px springgreen inset;*/
	border: thin solid black;
}
div.blue > * { width: 80%; margin: 0 0 0 5%; }
form > div.blue > textarea[name="code-input"], div.blue > #output { height: 450px; }
form > div.blue > textarea[name="code-input"] {
	display: block;
	resize: none;
	padding: 3px 4px;
	box-shadow: 0 0 5px 2px #444;
	word-wrap: normal;
	white-space: pre;
	border-width: 2px;
}
form > div.blue > textarea[name="code-input"]:focus { border-color: lawngreen; }
div.blue > #output {
	background: #F8F5FA;
	padding: 4px 5px;
	border: thin solid #888;
}
input[name="runCode"] {
	position: absolute;
	width: 100px;
	height: 26px;
	margin-top: calc(10% - 26px);
	right: 50%;
	padding: 2px 8px;
	font: bold 15px Arial;
	color: #333;
	background: rgb(207, 208, 219);
	background: linear-gradient(to bottom, #FAF8FB 10%, #AAADBF 100%);
	border: 1px solid #888;
	border-bottom: none !important;
	border-radius: 8px 8px 0 0;
	outline: none;
}
input[name="runCode"]:hover {
	background: linear-gradient(to bottom, #FAF8FB 10%, #A8ABCD 95%);
	color: black;
	border: 1px solid #555;
}
input[name="runCode"]:active {
	background: linear-gradient(to top, #FAF8FB 0%, #A8ABCD 90%);
	border: 2px groove #777;
}
</style>
<script src="/browserDetect.js"></script>
<script>
function getSelectedText() {
	if(window.getSelection) return window.getSelection().toString();
	else if(document.selection && document.selection.type != "Control") return document.selection.createRange().text;
	else return false;
}

$(document).ready(function() {
	$(document).keyup(function(e) {
		if(e.keyCode == 27 || (e.ctrlKey && e.which == 73)) {
			if(getSelectedText()) var code = getSelectedText();
			else var code = $('textarea[name="code-input"]').val();
			var lines = code.match(/\n/gm); lines = lines ? lines.length : 0;
			var letters = code.length -lines;
			if(letters == 0) lines -= 1;
			alert("Letters: "+letters+"\nLines: "+(lines+1));
		}
	});
	$('textarea[name="code-input"]').keydown(function(e) {
		var txtArea = $('textarea[name="code-input"]');
		var text = txtArea.val();
		var pointerIdx = txtArea.get(0).selectionStart;  //derzeitige Cursorposition
		if(e.which == 9 /*&& e.shiftKey*/) {  //TAB
			e.preventDefault();
			var text1 = text.substr(0, pointerIdx),  //Teil vor dem Cursor
				text2 = text.substring(pointerIdx, text.length);  //Teil danach
			text = text1 + "    " + text2;  // \t = Tab = 8 Leerzeichen
			txtArea.val(text);
			txtArea.get(0).selectionEnd = pointerIdx + 4;
		}
		if(getBrowser()!="Internet Explorer") {
			if(e.which == 8 || e.which == 37) {  //Löschtaste, Pfeil links
				var lineStart = text.substr(0, pointerIdx).lastIndexOf("\n") +1;
				var pointerEnd = txtArea.get(0).selectionEnd;
				var lineSpaces = text.substring(lineStart, pointerEnd);
				var spaceMatch = lineSpaces.match(/(    )+/);
				if(spaceMatch && lineSpaces == spaceMatch[0]) {
					e.preventDefault();
					if(e.which == 8) {
						var text1 = text.substr(0, pointerIdx-4),
							text2 = text.substring(pointerIdx, text.length);
						txtArea.val(text1 + text2);
					}
					txtArea.get(0).selectionEnd = pointerIdx - 4;
				}
			}
			if(e.which == 39) {  //Pfeil rechts
				if(text.substr(pointerIdx, 4)=="    ") {
					e.preventDefault();
					txtArea.get(0).selectionStart = pointerIdx + 4;
				}
			}
		}
		if(e.ctrlKey && e.which == 13) { $('form').eq(0).submit(); }
	});
})
</script>
</head>
<body>
<?php
$code = isset($_POST["code-input"]) ? trim($_POST["code-input"]) : "";
$code = isset($_GET["fn"]) && $code=="" ? file_get_contents('codebox-functions/'.$_GET["fn"].'.php') : preg_replace("/( )+\r\n/m", "\r\n", trim(@$_POST["code-input"], " \r\n"));
?>
<form action="" method="post">
	<input type="submit" name="runCode" value="Ausführen">
	<div class="blue">
		<textarea rows="30" name="code-input"><?= htmlspecialchars($code); ?></textarea>
	</div>
</form>
<div class="blue" style="border-left: none;">
	<div id="output">
	<?php
	if($code!="") {
		if($pos = stripos($code, "<?")!==false) {
			if($pos > 0) $code = "?>".$code;
			/*$code = preg_replace("/(^(<\?|<\?php)|(\?>)$)/", "", $code);*/
			eval($code);
		} elseif(preg_match("/^(\s*[a-zA-Z]{3,}|(\\$|#|\/\/|\/\*)[a-zA-Z]+)/", $code)) eval($code);
		else echo $code;
		echo "\n";
	}
	?>
	</div>
</div>
</body>
</html>
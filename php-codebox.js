function getSelectedText() {
    var curElem = document.activeElement;
    var fixElems = ['textarea', 'input'];
    if (fixElems.indexOf(curElem.tagName.toLowerCase()) > -1) {
        return curElem.value.substring(curElem.selectionStart, curElem.selectionEnd);
    } else if (window.getSelection) {
        return window.getSelection().toString();
    } else {
        return false;
    }
}

function runCode(code) {
    if (code) {
        $.post("", {code: code}, function(result) {
            window.frames[0].document.body.innerHTML = result;
        });
    }
}

$(document).ready(function() {
    // Element der Codebox
    var txtArea = document.querySelector('textarea.code');
    document.addEventListener("keyup", function(e) {
        // ESC oder Ctrl + I: Infos zum Text anzeigen
        if (e.which == 27 || (e.ctrlKey && e.which == 73)) {
            var code;
            if (getSelectedText()) {
                code = getSelectedText();
            } else {
                code = txtArea.value;
            }
            var lines = code.match(/\n/gm);
            lines = (lines) ? lines.length : 0;
            var letters = code.length - lines;
            if (letters == 0) lines -= 1;
            alert("Letters: " + letters + "\nLines: " + (lines + 1));
        }
    });
    txtArea.addEventListener("keydown", function(e) {
        /*
         * pointerIdx:  derzeitige Cursorposition oder Anfangsposition der Auswahl/Selection
         * pointerEnd:  Endposition der Auswahl
         * indentation: zu verwendende Charakter bzgl. Einrückung
         */
        var text = txtArea.value;
        var pointerIdx = txtArea.selectionStart;
        var pointerEnd = txtArea.selectionEnd;
        var indentation = "    ";

        // Tab: 'indentation' einfügen
        if (e.which == 9) {
            e.preventDefault();
            var textPrev = text.substr(0, pointerIdx),               // Teil vor dem Cursor
                textNext = text.substring(pointerEnd, text.length),  // Teil danach
                newPointerIdx = pointerIdx + indentation.length,
                selection;
            // block indentation
            if (selection = getSelectedText()) {
                if (e.shiftKey) {
                    selection = selection.replace(new RegExp("^(" + indentation + ")+", "gm"), function(match, $1) {
                        return match.replace($1, "");
                    });
                    // get the absolute position of the first non-whitespace character in selection
                    newPointerIdx = selection.search(/[^\s]/) + textPrev.length;
                } else {
                    selection = selection.replace(new RegExp('^', "gm"), indentation);
                }
                text = textPrev + selection + textNext;
            } else {
                text = textPrev + indentation + textNext;
            }
            txtArea.value = text;
            txtArea.selectionEnd = newPointerIdx;
        }
        if (!getSelectedText() && getBrowser() != "Internet Explorer") {
            // Tab-Simulation - Wenn ein Vielfaches von 4 Leerzeichen vor dem Cursor, lösche oder bewege den Cursor um 4, nicht um eins:
            // Löschtaste, Pfeiltaste links
            if (e.which == 8 || e.which == 37) {
                // Index/Position of first char from current line
                var lineStart = text.substr(0, pointerIdx).lastIndexOf("\n") + 1;
                var lineSpaces = text.substring(lineStart, pointerIdx);
                var spaceMatch = lineSpaces.match( new RegExp("(" + indentation + ")+") );
                // if regex matched and first match equals text from line start to cursor pos
                if (spaceMatch && spaceMatch[0] == lineSpaces) {
                    e.preventDefault();
                    if (e.which == 8) {
                        var textPrev = text.substr(0, pointerIdx - indentation.length),
                            textNext = text.substring(pointerIdx, text.length);
                        txtArea.value = textPrev + textNext;
                    }
                    txtArea.selectionEnd = pointerIdx - indentation.length;
                }
            }
            // Pfeiltaste rechts
            if (e.which == 39) {
                var regex = new RegExp("^(" + indentation + ")+(?! )");
                // chars after cursor pos must equal 'indentation' chars
                if (regex.test(text.substring(pointerIdx, text.length))) {
                    e.preventDefault();
                    txtArea.selectionStart = pointerIdx + indentation.length;
                }
            }
        }
        // Ctrl + Enter: Formular absenden
        if (e.ctrlKey && e.which == 13) {
            runCode(text);
        }
    });

    document.querySelector('.btn-submit').onclick = function() {
        txtArea.value = txtArea.value.trim();
        runCode(txtArea.value);
    }
})

/*
 * function for the browser Dev-Tools, offline regex101 alternative ;)
 * Usage: string to test regex against into the codebox, then call this function, with your regex as parameter, in the browser console
 */
function regexTester(regex) {
    var testStr = document.querySelector('textarea.code').value;
    testStr = testStr.split("\n");
    for (var i = 0; i < testStr.length; i++) {
        console.log(testStr[i].match(regex));
    }
}
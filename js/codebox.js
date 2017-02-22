// Element der Codebox
var txtArea = document.querySelector('textarea.code');
var lineNumbers = document.querySelector('.line-numbers');

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

function errorsOnLines(show) {
    show = (typeof show === "undefined") ? true : show;

    var errorLines = lineNumbers.querySelectorAll('.error');
    errorLines.forEach(function(elem) {
        elem.className = "";
    });
    if (show) {
        errorLines = window.outputFrame.document.querySelectorAll('.error-line');
        errorLines.forEach(function(elem) {
            var i = elem.innerHTML - 1;
            var lineToMark = lineNumbers.childNodes[i];
            if (lineToMark !== undefined) {
                lineToMark.className = "error";
            }
        });
    }
}

function runCode(code) {
    if (code) {
        $.post("", {code: code}, function(result) {
            var iframe = window.outputFrame.document;
            iframe.open();
            iframe.write(result);
            iframe.close();

            errorsOnLines();
        });
    }
}

function updateLineNumbers(newLineNumbers, removeOld) {
    // setTimeout puts the function in the queue and only fires after the last operation has finished
    // this way it will eg. fire after insertion of text and can calculate the correct line count including the copied text
    setTimeout(function() {
        removeOld = removeOld || false;
        newLineNumbers = newLineNumbers || txtArea.value.split("\n").length;
        var startLineNumber = lineNumbers.childNodes.length;
        if (removeOld) {
            lineNumbers.innerHTML = "";
            startLineNumber = 1;
        }
        if (newLineNumbers > startLineNumber || removeOld) {
            if (!removeOld) {
                startLineNumber += 1;
            }
            var nodes = document.createDocumentFragment();
            for (var i = startLineNumber; i <= newLineNumbers; i++) {
                var node = document.createElement('span');
                node.appendChild( document.createTextNode(i) );
                nodes.appendChild(node);
            }
            lineNumbers.appendChild(nodes);
        }
        else if (newLineNumbers < startLineNumber) {
            for (var i = startLineNumber; i > newLineNumbers; i--) {
                lineNumbers.removeChild(lineNumbers.lastElementChild);
            }
        }
    }, 0);
}


$(document).ready(function() {
    if (sessionStorage.getItem('autosave')) {
        txtArea.value = sessionStorage.getItem('autosave');
    }
    updateLineNumbers();
    autosize(txtArea);

    document.addEventListener("keydown", function(e) {
        // ESC oder Ctrl + I: Infos zum Text anzeigen
        if (e.which == 27 || (e.ctrlKey && e.which == 73)) {
            var code;
            if (getSelectedText()) {
                code = getSelectedText();
            } else {
                code = txtArea.value;
            }
            var lines = code.split("\n").length;
            var letters = code.length - (lines - 1);
            if (letters == 0) lines = 0;
            alert("Letters: " + letters + "\nLines: " + lines);
        }

        // Cmd + Shift + R: Clean reload, without session
        if (e.metaKey && e.shiftKey && e.which == 82) {
            e.preventDefault();
            sessionStorage.clear();
            location.reload();
        }
    });
    txtArea.addEventListener("keydown", function(e) {
        /*
         * pointerIdx:  derzeitige Cursorposition oder Anfangsposition der Auswahl/Selection
         * pointerEnd:  Endposition der Auswahl
         * indentation: zu verwendende Charakter bzgl. Einrückung
         */
        var text = txtArea.value,
            pointerIdx = txtArea.selectionStart,
            pointerEnd = txtArea.selectionEnd,
            selection = getSelectedText(),
            indentation = "    ",
            indentLen = indentation.length,
            indentationRegex = "^( {4}|\t|\u2002{4})"; // four spaces, one tab, four &ensp; characters

        // Tab: 'indentation' einfügen
        if (e.which == 9) {
            e.preventDefault();
            var textPrev = text.substr(0, pointerIdx),  // Teil vor dem Cursor
                textNext = text.substring(pointerEnd),  // Teil danach
                newPointerIdx = pointerIdx + indentLen;

            // block indentation
            if (selection) {
                if (e.shiftKey) {
                    selection = selection.replace(new RegExp(indentationRegex, "gm"), "");
                    // get the absolute position of the first non-whitespace character in selection
                    newPointerIdx = selection.search(/[^\s]/) + textPrev.length;
                } else {
                    selection = selection.replace(new RegExp('^', "gm"), indentation);
                }
                text = textPrev + selection + textNext;
            } else {
                if (e.shiftKey) {
                    textPrev = text.substr(0, pointerIdx - indentLen);
                    var indent = text.substr(pointerIdx - indentLen, indentLen);

                    text = textPrev + indent.replace(new RegExp(indentationRegex), "") + textNext;
                    newPointerIdx = pointerIdx - indentLen;
                } else {
                    text = textPrev + indentation + textNext;
                }
            }
            txtArea.value = text;
            txtArea.selectionEnd = newPointerIdx;
        }

        if (!selection) {
            // Tab-Simulation - Wenn ein Vielfaches von 4 Leerzeichen vor dem Cursor, lösche oder bewege den Cursor um 4, nicht um eins:
            // Löschtaste, Pfeiltaste links
            if (e.which == 8 || e.which == 37) {
                // Index/Position of first char from current line
                var lineStart = text.substr(0, pointerIdx).lastIndexOf("\n") + 1,
                    lineBeforePointer = text.substring(lineStart, pointerIdx),
                    regex = new RegExp(indentationRegex + "+$");

                // if text from line start to cursor pos consists only of multiples of indentation
                if (regex.test(lineBeforePointer)) {
                    e.preventDefault();
                    if (e.which == 8) {
                        var textPrev = text.substr(0, pointerIdx - indentLen),
                            textNext = text.substring(pointerIdx, text.length);
                        txtArea.value = textPrev + textNext;
                    }
                    txtArea.selectionEnd = pointerIdx - indentLen;
                }
                // Zeilennummern anpassen, wenn Zeile entfernt wurde
                else if (e.which == 8 && !lineBeforePointer && pointerIdx > 0) {
                    lineNumbers.removeChild(lineNumbers.lastElementChild);
                }
            }
            // Pfeiltaste rechts
            if (e.which == 39) {
                var regex = new RegExp(indentationRegex);
                // chars after cursor pos must equal 'indentation' chars
                if (regex.test(text.substring(pointerIdx, text.length))) {
                    e.preventDefault();
                    txtArea.selectionStart = pointerIdx + indentLen;
                }
            }
        }

        if (selection && !e.ctrlKey && !e.metaKey) {
            updateLineNumbers();
        }

        // Enter
        if (e.which == 13) {
            // Ctrl + Enter: Formular absenden
            if (e.ctrlKey) {
                runCode(text);
            }
            // Zeilennummern anpassen, wenn neue Zeile
            else {
                updateLineNumbers();
            }
        }

        // Ctrl + S
        if (e.ctrlKey && e.which == 83) {
            sessionStorage.setItem('autosave', txtArea.value);
        }

        /*if (!e.ctrlKey && !e.metaKey && e.which != 27) {
            errorsOnLines(false);
        }*/
    });

    txtArea.onpaste = txtArea.oncut = function() {
        updateLineNumbers();
    };

    txtArea.addEventListener("autosize:resized", function() {
        lineNumbers.style.height = txtArea.style.height;
    });

    // standard POST form submit (with page reload)
    document.querySelector('.btn-submit').onclick = function() {
        txtArea.value = txtArea.value.trim();
        sessionStorage.setItem('autosave', txtArea.value);

        var form = document.createElement('form');
        form.name = 'codebox';
        form.method = 'post';
        form.style.display = 'none';

        var formTxtArea = txtArea.cloneNode(false);
        formTxtArea.value = txtArea.value;
        form.appendChild(formTxtArea);

        document.body.appendChild(form);
        document.codebox.submit();
    }
});


/*
 * function for the browser Dev-Tools, offline regex101 alternative ;)
 * Usage: string to test regex against into the codebox, then call this function, with your regex as parameter, in the browser console
 */
function regexTester(regex) {
    var testStr = txtArea.value;
    console.log(testStr.match(regex));
}
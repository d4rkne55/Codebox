var txtArea = document.querySelector('.codebox-wrapper .code');
var lineNumbers = document.querySelector('.codebox-wrapper .line-numbers');

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

function updateLineNumbers(newLineNumbers, removeOld) {
    // setTimeout puts the function in the queue and only fires after the last operation has finished
    // this way it will eg. fire after insertion of text and can calculate the correct line count including the pasted text
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

        // update textarea height
        var txtAreaActualHeight = txtArea.scrollHeight;
        var codeboxVisibleHeight = document.querySelector('.codebox-wrapper').clientHeight;

        if (txtAreaActualHeight > codeboxVisibleHeight) {
            // height = line * line-height + vertical padding
            var height = lineNumbers.childNodes.length * 16 + 20;
            txtArea.style.height = height + 'px';
            lineNumbers.style.height = height + 'px';
        }
    }, 0);
}

function runCode(code) {
    if (!code) {
        return false;
    }

    $.post('', { code: code }, function(output) {
        var iframe = document.outputFrame.document;
        iframe.open();
        iframe.write(output);
        iframe.close();

        errorsOnLine();
    })
}

function errorsOnLine() {
    // clear previous errors
    var errorLines = lineNumbers.querySelectorAll('.error');
    errorLines.forEach(function(elem) {
        elem.className = '';
    });

    var errors = document.outputFrame.document.querySelectorAll('.code-error .error-line');
    errors.forEach(function(elem) {
        var errorLine = parseInt(elem.innerHTML) - 1;
        var lineToMark = lineNumbers.childNodes[errorLine];

        if (lineToMark !== undefined) {
            lineToMark.className = 'error';
        }
    });
}


$(document).ready(function() {
    if (sessionStorage.getItem('autosave')) {
        txtArea.value = sessionStorage.getItem('autosave');
    }
    updateLineNumbers();

    //autosize(txtArea);

    document.addEventListener('keydown', function(e) {
        // CTRL + I or ESC
        if ((e.ctrlKey && e.which == 73) || e.which == 27) {
            var code;
            if (getSelectedText()) {
                code = getSelectedText();
            } else {
                code = txtArea.value;
            }

            var lineCount = code.split("\n").length;
            var letterCount = code.length - lineCount + 1;

            alert('Letters: ' + letterCount + "\nLines: " + lineCount);
        }

        // CTRL + Shift + R
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.which == 82) {
            e.preventDefault();
            sessionStorage.clear();
            location.reload();
        }
    });

    txtArea.addEventListener('keydown', function(e) {
        var code = txtArea.value;
        var pointerIdx = txtArea.selectionStart;
        var pointerEnd = txtArea.selectionEnd;
        var indentation = '    ';
        var indentationRegex = new RegExp('^(' + indentation + ')+');

        // when selection and any key except CTRL or CMD (Mac)
        if (pointerIdx != pointerEnd && !e.ctrlKey && !e.metaKey) {
            updateLineNumbers();
        }

        // left arrow key, backspace
        if (e.which == 37 || e.which == 8) {
            if (pointerIdx != pointerEnd) {
                return false;
            }

            var lineStart = code.lastIndexOf("\n", (pointerIdx - 1)) + 1;
            var beforeCursor = code.substring(lineStart, pointerIdx);
            var regex = new RegExp(indentationRegex.source + '$');

            if (regex.test(beforeCursor)) {
                e.preventDefault();

                // backspace
                if (e.which == 8) {
                    var prevLines = code.substring(0, lineStart);
                    var newIndent = beforeCursor.slice(0, -indentation.length);
                    var afterCursor = code.substr(pointerIdx);

                    txtArea.value = prevLines + newIndent + afterCursor;
                }

                txtArea.selectionStart = pointerIdx - indentation.length;
                txtArea.selectionEnd = txtArea.selectionStart;
            } else {
                // backspace
                if (e.which == 8 && beforeCursor == '') {
                    updateLineNumbers();
                }
            }

            return;
        }

        // right arrow key
        if (e.which == 39) {
            var afterCursor = code.substr(pointerIdx);

            if (indentationRegex.test(afterCursor)) {
                e.preventDefault();

                txtArea.selectionStart = pointerIdx + indentation.length;
                txtArea.selectionEnd = txtArea.selectionStart;
            }

            return;
        }

        // Tab
        if (e.which == 9) {
            e.preventDefault();

            var beforeCursor = code.substring(0, pointerIdx);
            var afterCursor = code.substr(pointerEnd);

            // when selection
            if (pointerIdx != pointerEnd) {
                var selection = getSelectedText();

                // Shift + Tab
                if (e.shiftKey) {
                    var regex = new RegExp('^' + indentation, 'gm');
                    selection = selection.replace(regex, '');

                    pointerEnd -= indentation.length;
                } else {
                    selection = selection.replace(/^/gm, indentation);
                    var indents = selection.split("\n").length;

                    pointerEnd += indents * indentation.length;
                }

                txtArea.value = beforeCursor + selection + afterCursor;
                txtArea.selectionStart = pointerIdx;
                txtArea.selectionEnd = pointerEnd;
            } else {
                if (!e.shiftKey) {
                    txtArea.value = beforeCursor + indentation + afterCursor;
                    txtArea.selectionStart = pointerIdx + indentation.length;
                    txtArea.selectionEnd = txtArea.selectionStart;
                }
            }

            return;
        }

        // Enter
        if (e.which == 13) {
            // CTRL + Enter
            if (e.ctrlKey) {
                runCode(code);
            } else {
                updateLineNumbers();
            }

            return;
        }

        // CTRL + S
        if (e.ctrlKey && e.which == 83) {
            e.preventDefault();
            sessionStorage.setItem('autosave', code);
        }
    });

    txtArea.oncut = txtArea.onpaste = function() {
        updateLineNumbers();
    };

    txtArea.addEventListener('autosize:resized', function() {
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
})
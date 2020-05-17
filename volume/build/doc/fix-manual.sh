#!/bin/bash
#
# Fix the manual so it is also displayed in firefox
sed -i 's/<script src="LaTeXML-maybeMathjax.js" type="text\/javascript"\/>/<script src="LaTeXML-maybeMathjax.js" type="text\/javascript"><\/script>/' manual.tex.xhtml

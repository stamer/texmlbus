#!/bin/bash
#
# Script to call pagelimit.php
# Released under MIT License
# (c) 2020 Heinrich Stamerjohanns
#
#
FILE="$1"
RESULTFILE="$2"
STDOUTLOG='pagelimit.stdout.log'
STDERRLOG='pagelimit.stderr.log'

SCRIPT_DIR="$(dirname "$(readlink -f "$0")")"

if [[ -e "${FILE}" ]]; then
    FLATFILE="${FILE}.flattened"
    latexpand --explain --keep-comments --makeatletter -o "${FLATFILE}" "${FILE}" 2> "${STDERRLOG}"
	RES=$?
	if [[ "$RES" == "1" ]]
	then
	    echo "Error with latexpand!"
	    echo "Error with latexpand!" >> ${STDERRLOG}
	    exit $RES
	fi
	php "${SCRIPT_DIR}/pagelimit.php" "${FLATFILE}" "${RESULTFILE}" > "${STDOUTLOG}" 2>> "${STDERRLOG}"
	RES=$?
else
	RES=2
fi

exit $RES

#!/bin/sh

LISTREFERER=../referral-spam.conf
echo '# WARNING! This file was generated. Do not change!' > "${LISTREFERER}"
echo 'map $http_referer $block_referer {' >> "${LISTREFERER}"
echo 'default 0;' >> "${LISTREFERER}"
cat  ./domains.txt | sort | uniq | awk 'NF {gsub("\.","\.",$1);gsub("\-","\-",$1);print "\"~*"$1"\" 1;"}' >> "${LISTREFERER}"
echo '}' >> "${LISTREFERER}"

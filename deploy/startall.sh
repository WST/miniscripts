#!/bin/bash

# © Ilya I. Averkov <WST>
# http://averkov.net

for i in `find /home -mindepth 4 -maxdepth 4 -name start.sh`; do
	USERNAME=`echo $i | cut -d/ -f3`;
	DOMAIN=`echo $i | cut -d/ -f5`;
	echo "Starting $DOMAIN"
	cd `dirname $i`;
	su $USERNAME -c "./start.sh";
done;

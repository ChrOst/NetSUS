#!/bin/bash
# This installer installs vnstat + vnstati and
# configures the cronjob which updates the graphics within the web-directory
detectedOS=$1

packages="vnstat vnstati" #
imgpath="/var/www/webadmin/images/vnstat/"
cronjob="vnstati.cron"
cronfile="gen-vnstati-images"


if [[ "$detectedOS" == 'Ubuntu' ]]; then
	# Install packages
	apt-get -qq -y install ${packages}
	# Configure vnstat that it just saves any value it gets.
	# Per Default it's limiting to 100 Mbit usually. We don't want that
	# Some Machines may have 1Gbit or 10Gbit cards
	sed -i.bak "s/MaxBandwith.*/MaxBandwith\ 0/" /etc/vnstat.conf
	# Initialize the Database for all network interfaces
	for Interface in $(ls /sys/class/net/)
	do
		vnstat -u -i ${Interface} >/dev/null 2>&1
	done
	# Create vnstat images directory
	mkdir -p ${imgpath}
	# Install cronjob
	cp files/${cronjob} /etc/cron.d/
	cp files/${cronfile} /bin/
	chmod +x /bin/${cronfile}
fi



#if [[ "$detectedOS" == 'CentOS' ]] || [[ "$detectedOS" == 'RedHat' ]]; then
#
#fi

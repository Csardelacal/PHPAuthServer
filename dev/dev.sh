#!/bin/bash
# https://serverfault.com/questions/874213/add-temporary-entry-to-hosts-when-tunnelling-ssh

function control_c {
    echo -en "\n## Caught SIGINT; Clean up /etc/hosts and Exit \n"
	umount -f /etc/hosts
	rm /tmp/hosts
    exit $?
}

trap control_c SIGINT
trap control_c SIGTERM

if [ -f /tmp/hosts ]; then
	rm /tmp/hosts
fi

cp /etc/hosts /tmp/hosts
echo "127.0.0.1 phpauth.local" >> /tmp/hosts
mount --bind /tmp/hosts /etc/hosts

docker compose up $@

umount -f /etc/hosts
rm /tmp/hosts

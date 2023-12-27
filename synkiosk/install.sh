#!/bin/bash

apt-add-repository universe -y
apt update && apt upgrade -y

apt install -y mc curl htop nano git wget openssh-server nmap curl awscli
apt install -y dnsutils net-tools inetutils-ping
apt install -y software-properties-common
apt install chromium-browser -y
apt install -y gnome-tweaks gnome-shell-extensions chrome-gnome-shell
apt install -y php8.1 php8.1-cli php8.1-curl

ufw disable

cd ~
wget https://raw.githubusercontent.com/politsin/snipets/master/sh/.bash_profile
rm ~/.bashrc
wget https://raw.githubusercontent.com/politsin/snipets/master/sh/.bashrc

#Composer:::
wget https://getcomposer.org/installer -q -O composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer

service apache2 stop
apt remove apache2 -y
apt autoremove

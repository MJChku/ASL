#!/bin/bash

# install ufw
sudo apt install ufw -y

# configure ufw
sudo ufw allow ssh
sudo ufw allow from 192.168.20.2 to any port 6514 proto tcp

# enable ufw
sudo ufw enable


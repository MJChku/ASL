#!/bin/bash

# install ufw
sudo apt update && sudo apt install ufw -y

# configure ufw
sudo ufw allow http
sudo ufw allow https
sudo ufw allow ssh

# enable ufw
sudo ufw enable


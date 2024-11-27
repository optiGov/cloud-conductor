#!/bin/bash

# wait for network to be ready
sleep 30

# run iptables commands
iptables -F -t filter

iptables -A INPUT -p icmp              -j ACCEPT
iptables -A INPUT -i lo                -j ACCEPT
iptables -A INPUT -m state --state E,R -j ACCEPT
iptables -A INPUT -p esp               -j ACCEPT
iptables -A INPUT -p tcp --dport 22    -m state --state NEW -j ACCEPT
iptables -A INPUT -p udp --dport 500   -m state --state NEW -j ACCEPT
iptables -A INPUT -p udp --dport 4500  -m state --state NEW -j ACCEPT

iptables -P INPUT   DROP
iptables -P FORWARD DROP

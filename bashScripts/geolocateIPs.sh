#!/bin/bash
FILE="ipget.csv"
while read count ip; do
        url=http://ip-api.com/csv/$ip
        result=$(curl --request GET $url)
        echo "$ip,$count,$url,$result"
done >$FILE

WhoIsExploration
================

Initial Problem
================
I currently run a DigitalOcean droplet, I decided to check my auth.log to see who was trying to crack my passwords. It turns out I was getting spammed with requests, and my auth.log file was many thousands of lines long in just the last 3 days.

However, I wanted to get the most important information from these log files, the orginator's IP. So I messed around with Linux commands and regular expressions until I devised these commands

```bash
  sudo grep 'authentication failure' /var/log/auth.log
```
[grep](http://unixhelp.ed.ac.uk/CGI/man-cgi?grep) in this case is used to grab every line in the auth.log file that contains the string 'authentication failure'

```vim
    Nov 17 09:03:40 Storipedia sshd[13820]: pam_unix(sshd:auth): authentication failure; logname= uid=0 euid=0 tty=ssh ruser= rhost=115.236.179.140  user=root
```
```bash
    sudo awk 'match($0,/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/) {print substr($0, RSTART, RLENGTH)}' /var/log/auth.log
```
[awk](http://unixhelp.ed.ac.uk/CGI/man-cgi?awk) is another pattern scanning tool, however with the code above I can isolate regular expressions that I've found, the regular expression that I've written looks for <IP Address>.

It is very important that you do apt-get install gawk
```vim
    115.236.179.140
```
Unfortunately, this does not yet account for duplicates, so the same IP may be repeated multiple times

```bash
    sort -n
```
[sort](http://unixhelp.ed.ac.uk/CGI/man-cgi?sort) will sort all the lines compared to the string numerical value, which is in preperation for the next command

```bash
    uniq -c
```
[uniq](http://unixhelp.ed.ac.uk/CGI/man-cgi?uniq) will actually discard duplicates, and the option -c will keep a running count of the number of duplicates found

```bash
    sed -e 's/^[ \t]*//'
```
[sed](http://unixhelp.ed.ac.uk/CGI/man-cgi?sed) is used to replace strings with another string. In this case we add a script with -e that follows the format s/regexp/replacement/ such that we are replacing all leadings spaces with nothing.
```bash
    > authFailure.txt
```

This command sends all the output into the authFailure.txt file

```bash    
    sudo grep 'authentication failure' /var/log/auth.log | sudo awk 'match($0,/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/) {print substr($0, RSTART, RLENGTH)}' | sort -n | uniq -c | sed -e 's/^[ \t]*//' > authFailure.txt
```
Each of the noted commands is piped into the other and then finally into authFailure.txt. 

Resulting Scripts
=================
With the information I've learned from before I've developed three seperate scripts

getRootAttemptIPs => Outputs the number of authentication failures from an IP

getInvalidUserIPs => Outputs the number of Invalid user failures from an IP

getInvalidUserNames => Outputs all the invalid usernames IPs tried to use

Bringing Down the Banhammer
==================
 Now that I have a list of all these IPs that are spamming me, I can simply add an IP address to a blacklist by
 ```bash
     sudo iptables -A INPUT -s <IP Address> -j DROP
 ```
 This will append the IP to the iptable and always drop any packets recieved from the noted IP address.
 
Long Term Solution
==================
Unfortunately, I don't have the time to check the logs that often, so a utility called [fail2ban](https://help.ubuntu.com/community/Fail2ban) can be utilized. With this I can have IPs banned for too many bad authentications, in my case 5 within 30 minutes. While this won't prevent all unwanted requests, it will stop spammers such as 103.41.124.50 who had 33621 bad authentication errors in just the last 3 days.


So who were those IPs?
===================
A question remains of who exactly were those IPs located, where can spammers live to bother us without fear?

There is a website http://ip-api.com/ which is a free and open geolocator API, with this we can learn more about the IPs that spammed me.

However, I thought why not get that geolocation data straight from bash, so I wrote a bash script that would use curl to grab the geolocational data of each IP address.

```bash
#!/bin/bash
FILE="ipget.csv"
while read count ip; do
        url=http://ip-api.com/csv/$ip
        result=$(curl --request GET $url)
        echo "$ip,$count,$url,$result"
done >$FILE
```
This bash script results in the output ipget.csv which now contains an IP adress, a count of attempts, and country of origin.

Converting to JSON
=================
My next goal was to convert this csv file to html friendly JSON data, this was implemented through getIPgeoJSON.php. This method would always output the JSON encoding of that CSV data to any empty GET request.

One very useful PHP method was [fgetcsv](http://php.net/manual/en/function.fgetcsv.php) which converted a line in the CSV file to an array which each seperate value in its own index. This made it very simple building the JSON array construct.


Visually Representing the Spammers
===============================
Finally, I used the [Google charts API](https://google-developers.appspot.com/chart/interactive/docs/gallery/geochart) to create a chart that shows where in the world all the requests are coming from. On completion of the AJAX request to getIPgeoJSON.php the javascript will dynamically build an array that accumulates the total attempts for each country found. 

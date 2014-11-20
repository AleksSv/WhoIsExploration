WhoIsExploration
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
    sudo awk 'match($0,/rhost=[0-9]*.[0-9]*.[0-9]*.[0-9]*/) {print substr($0, RSTART+6, RLENGTH-6)}' /var/log/auth.log
```
[awk](http://unixhelp.ed.ac.uk/CGI/man-cgi?awk) is another pattern scanning tool, however with the code above I can isolate regular expressions that I've found, the regular expression that I've written looks for rhost=<IP Address>.
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
    sudo grep 'authentication failure' /var/log/auth.log | sudo awk 'match($0,/rhost=[0-9]*.[0-9]*.[0-9]*.[0-9]*/) {print substr($0, RSTART+6, RLENGTH-6)}' /var/log/auth.log | sort -n | uniq -c | sed -e 's/^[ \t]*//' > authFailure.txt
```
Each of the noted commands is piped into the other and then finally into authFailure.txt. The result of this operation is

```
18 [0-9]
54 lea
6 out16
3 1.235.166.11
473 58.83.146.252
10 64.34.39.111
4 91.240.163.39
687 103.41.124.22
1074 103.41.124.40
2578 103.41.124.48
13446 103.41.124.50
25 104.131.150.105
15 115.236.179.140
598 117.21.173.29
1 118.123.206.70
56 119.9.72.75
1 162.213.153.135
2317 209.92.176.31
387 211.147.242.113
4 218.106.254.121
22 220.177.198.39
306 220.177.198.93
568 222.186.34.116
32 222.186.34.202
598 222.186.34.244
26 222.186.56.46
76 222.219.187.9
```

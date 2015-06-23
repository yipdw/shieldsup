# Shields Up
Shields Up -- Preventing Twitter Dog-Piling

A live version of this tool can be found at http://shieldsup.onlineabuseprevention.org

## Aims
While [Good Game Auto Blocker](http://github.com/oapi/ggautoblocker) is a valuable tool, it was designed with a very specific pattern in mind: blocking a group of people that center their interactions around several accounts that lead the harassing behavior.

Shields Up aims to mitigate the flood of tweets that can inundate a user when a *single* account publicly mentions their twitter handle. Instead of using a follower list as a source for accounts to block, we build our list based off of interactions such as retweets of the offending user.

These lists are not necessarily meant to be shared via blocktogether, but are aimed to be dynamically created per-user in a format that allows for enabling and disabling of these blocks at will, per the new Twitter functionality of importing block lists.

Quite a bit of work still needs to be done, and this is in alpha. The user interface is very basic, and Twitter itself is lacking quite a few features. Twitter block import is limiting the blocks to 5000 accounts for the time being, and there isn't a way to reverse the blocks. 

## Warning
It is *highly recommended* that you export your blocks from Twitter before importing these, so you can reset your blocks at a later date.

This code also doesn't yet verify if any of these accounts are in your friends list. We've got a huge list of features that we're working on, and we are accepting external contributors. Check out our Issues list if you want to help out!

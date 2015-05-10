# shieldsup
Shields Up -- Preventing Twitter Dog-Piling

While [Good Game Auto Blocker](http://github.com/oapi/ggautoblocker) is a valuable tool, it was designed with a very specific pattern in mind: blocking a group of people that center their interactions around several accounts that lead the harassing behavior.

Shields Up aims to mitigate the flood of tweets that can inundate a user when a *single* account publicly mentions their twitter handle. Instead of using a follower list as a source for accounts to block, we build our list based off of interactions such as retweets of the offending user.

These lists are not necessarily meant to be shared via blocktogether, but are aimed to be dynamically created per-user in a format that allows for enabling and disabling of these blocks at will. 

Coming soon. ;)

### utilities

#### keymanager

Manage Berkeley DB of Twitter OAuth user tokens

##### usage

```
# add a token/secret to the database
$ ./keymanager.rb add -t <oauth token> -s <oauth secret>

# list all tokens
$ ./keymanager.rb list
```

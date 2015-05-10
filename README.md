# shieldsup
Shields Up -- Block List generator for Twitter

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

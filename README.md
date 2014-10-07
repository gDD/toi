# toi - Texting over Internet

## Server Setup

Configure `/server/public` as root directory

## Client Setup

Asterisk's extensions.conf configuration:

    # ...

    [dongle-incoming-sms]
    exten => sms,1,Noop(Incoming SMS from ${CALLERID(num)} ${BASE64_DECODE(${SMS_BASE64})})
    exten => sms,n,System(cd /home/username/client && ./sms_inbox.lua ${DONGLENUMBER} ${CALLERID(num)} ${EPOCH} ${SMS_BASE64})
    exten => sms,n,Hangup()

    # ...
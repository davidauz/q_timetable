# q_timetable

A simple timetable app made in quasar, sqlite3 and php.

See sample-crontab to send the digest.


## Install
First install the dependencies:

```bash
yarn
# or
npm install
```

This projects uses PHPMailer to send emails.

The preferred way to install PHPMailer is with composer so if you don't have it you have to install it first:
```bash
curl -sS https://getcomposer.org/installer | php
```

Use composer to install PHPMailer: 
```bash
composer update
```

You will also need sqlite3 so install the following packages: 
```bash
apt install sqlite3
apt install php-sqlite3
```

### development mode
```bash
quasar dev
```

### Deployment
```bash
quasar build
```

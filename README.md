# Send an Email Alert when SSL is Nearing Certificate Expiry

## Setup

Clone this repo then
```sh
composer install
```

Copy `config/env.example` to `config/.env`  and configure your SMTP settings

```ini
# use mailgun or similar
SMTP_HOST="smtp.example.com"
SMTP_USER="user@example.com"
SMTP_PASS="pass1234"
SMTP_PORT=587
SMTP_NOTIFY_EMAIL="joe@example.net"
SMTP_NOTIFY_NAME="Joe User"
SMTP_FROM_EMAIL="no-reply@example.com"
SMTP_FROM_NAME="Cert Checker"
```

Copy `config/config.example.php` to `config/config.php` and set urls to check and a default days value

```php
return [
    'urls' => [
        'https://google.com',
        'https://cisco.com'
    ],
    // if the remaining days certicate life is less than this a notification will be sent
    'days' => 14
];
```

## Usage

**Run using composer**

```sh
# composer check -- <days>
cd /path/to/cert-checker

# use config/config.php default days value
composer check

# override days left before notification value on command line
composer check -- 30
```


**Using `-d` syntax**


```sh
# specify days
composer -d /path/to/cert-checker check -- 30

# or to use the days default value set in config/config.php
composer -d /path/to/cert-checker check
```


**Via Cron**

Every day at 3:35AM run SSL Expiry check

```
# specify days
35 3 * * * composer -d /path/to/cert-checker check -- 30 >> /tmp/cert-checker.log 2>&1

# use default from config.php
35 3 * * * composer -d /path/to/cert-checker check >> /tmp/cert-checker.log 2>&1
```
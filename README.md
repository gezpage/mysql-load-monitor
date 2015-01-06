# MySQL Load Monitor

I wrote this script in a rush when our MySQL server started blocking my
PHP application because of (at the time) unknown reasons. The sole
intention is to warn when a large number of MySQL processes get queued
up which is often a cause for concern.

It will send you an email with a very basic var_dump() style list of the
MySQL processes and the number that is queued.

This is not intended to be used permanently but can at least warn you in
desperate times so you are prepared for calls from people that cannot
access your website!

## Set up

Copy mysql_load_check_config.dist to mysql_load_check_config.php and
edit it, filling in your database and email configuration.

The most important setting is MIN_PROCSSES_TO_WARN which will determine
if it should raise an alert or not. In MySQL enter SHOW PROCESSLIST; to
see what your idle process count looks like and make a judgement on what
you want it to alert on. On a busy server you might only care if the
processes get backed up over 100, to be alerted when it's most likely
that a rogue SQL query has locked tables and is causing a bottleneck.

If you only wish to test it on the command line, you can disable the
email functionality with EMAIL_ENABLED.

## Using

The script is meant to be run on the console or in Crontab. 

### Using on the console

Simply run it with the php executable:

    php mysql_load_check.php

If you are only testing set the MIN_PROCSSES_TO_WARN quite low so you
can trigger an warning.

### Automatic load checks with Crontab

Using with crontab is only useful if email alerts are enabled, so check
this is working on the console (as above) first.

On the linux console enter the crontab edit page with:

    crontab -e

and enter a line like so to check every 5 minutes:

    */5 * * * * php /path/to/files/mysql_load_check.php

Save and exit the crontab editor and as a sanity check choose a low
MIN_PROCSESES_TO_WARN setting and ensure alert emails are coming
through, then change it to the real setting you wish to warn on.

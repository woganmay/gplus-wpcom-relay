gplus-wpcom-relay
=================

Pretty simple: A PHP script that fetches public Shares from Google+, and posts them to wordpress.com by using the Post-by-email feature. Designed to be automated (called from /etc/crontab for instance), and keeps track of which posts have already been emailed to prevent duplication.

By: [Wogan](http://wogan.me)

Config & Setup
=================
The config.php file is well-commented.

To actually make this integration work you'll need a Simple API Key for an API project on Google. Start here: https://code.google.com/apis/console/

License
=================
WTFPL
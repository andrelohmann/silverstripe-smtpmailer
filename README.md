## Fork of http://www.silverstripe.org/smtpmailer-module/

## Requirements

Silverstripe 3.3.x

## Installation

add the following to your _ss_environment.php

```php
// smtpmailer
define('SMTPMAILER', json_encode([
	"charset_encoding" => "utf-8", // E-mails characters encoding, e.g. : 'utf-8' or 'iso-8859-1'
	"debug_level" => "0", // Print debugging informations. 0 = no debuging, 1 = print errors, 2 = print errors and messages, 4 = print full activity
	"credentials" => [
		"default" => [
			"server_address" => "smtp.gmail.com", // SMTP server address
			"server_port" => "465", // SMTP server port. Set to 25 if no encryption or tls. Set to 465 if ssl
			"secure_connection" => "ssl", // SMTP encryption method : Set to '' or 'tls' or 'ssl'
			"do_authenticate" => true, // Turn on SMTP server authentication. Set to false for an anonymous connection
			"username" => "__EMAILADDRESS__", // SMTP server username, if do_authenticate == true
			"password" => "__PASSWORD__", // SMTP server password, if do_authenticate == true
			"from" => "__EMAILADDRESS__" // From Address: e.g. "My Name" <my.account@gmail.com>, optional, use when from address needs to be fixed
		],
		"log" => false // false or array with credentials
	]
]));

// Error Log Email Address
define('LOG_EMAIL','__EMAIL__'); // logs will be send to this address, if defined
```
default and log credentials are mendatory. if you want to offer different smtp gateways inside your app, just add them to the credentials array.

selecting a special credentials set for an email is done by setting the credentials config
```php
Config::inst()->update('SmtpMailer', 'credentials', '__CREDENTIALS_SET__'); // "default" by default
```

### Notice
This repository uses the git flow paradigm.
After each release cycle, do not forget to push tags, master and develop to the remote origin
```
git push --tags
git push origin develop
git push origin master
```
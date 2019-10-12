# DotEnv

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.org/originphp/dotenv.svg?branch=master)](https://travis-ci.org/originphp/dotenv)
[![coverage](https://coveralls.io/repos/github/originphp/dotenv/badge.svg?branch=master)](https://coveralls.io/github/originphp/dotenv?branch=master)

A dotenv parser.

## Installation

To install this package

```linux
$ composer require originphp/dotenv
```


Then in your application bootstrap

```php
use Origin\DotEnv\DotEnv;
(new DotEnv())->load(CONFIG);
```

Here is an example of a dotenv file.

```linux
GMAIL_USERNAME=foo@gmail.com
GMAIL_PASSWORD=secret
```

You can also add `export` in front of each line so you can source the file with bash.

```linux
export GMAIL_USERNAME=foo@gmail.com
export GMAIL_PASSWORD=secret
```

If you want to use a custom name

```php
use Origin\DotEnv\DotEnv;
(new DotEnv())->load(CONFIG,'.env-local');
```

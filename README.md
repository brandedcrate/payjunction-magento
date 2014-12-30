# PayJunction Magento Extension

This extension adds PayJunction as an optional payment method for magento. It is known to work on Magento v1.9 although it may work on earlier versions.

## Installation
### Manual install
Download a [released package](http://brandedcrate-releases.s3-website-us-west-1.amazonaws.com/?prefix=payjunction-magento/) and install from the command line:
~~~bash
$ ./mage install-file Payjunction_Magento-0.1.0.tgz
~~~

## Development Setup
Install development dependencies
~~~bash
$ ./composer.phar install
~~~

If you want to work on this extension, then start with a working magento installation, clone this repository and run the dev task pointing it to your magento installation which in this case lives in ../magento-test:
~~~bash
$ ./bin/dev ../magento-test
~~~

When you're ready to build, run the build script and the new package will appear in ./dist:
~~~bash
$ ./bin/build
~~~

edwrodrig\google_utils
========
A php library to read some Google Services

[![Latest Stable Version](https://poser.pugx.org/edwrodrig/google_utils/v/stable)](https://packagist.org/packages/edwrodrig/google_utils)
[![Total Downloads](https://poser.pugx.org/edwrodrig/google_utils/downloads)](https://packagist.org/packages/edwrodrig/google_utils)
[![License](https://poser.pugx.org/edwrodrig/google_utils/license)](https://packagist.org/packages/edwrodrig/google_utils)
[![Build Status](https://travis-ci.org/edwrodrig/google_utils.svg?branch=master)](https://travis-ci.org/edwrodrig/google_utils)
[![codecov.io Code Coverage](https://codecov.io/gh/edwrodrig/google_utils/branch/master/graph/badge.svg)](https://codecov.io/github/edwrodrig/google_utils?branch=master)
[![Code Climate](https://codeclimate.com/github/edwrodrig/google_utils/badges/gpa.svg)](https://codeclimate.com/github/edwrodrig/google_utils)

## My use cases

 * Reading resources like images from google drive.
 * Read google spreadsheets and export them as a json  

My infrastructure is targeted to __Ubuntu 16.04__ machines with last __php7.2__ installed from [ppa:ondrej/php](https://launchpad.net/~ondrej/+archive/ubuntu/php).
I use some unix commands for some process like __cp__ or __ln__.
I'm sure that there are way to make it compatible with windows but I don't have time to program it and testing,
but I'm open for pull requests to make it more compatible.

## Documentation
The source code is documented using [phpDocumentor](http://docs.phpdoc.org/references/phpdoc/basic-syntax.html) style,
so it should pop up nicely if you're using IDEs like [PhpStorm](https://www.jetbrains.com/phpstorm) or similar.

## Composer
```
composer require edwrodrig/google_utils
```

## Testing
The test are built using PhpUnit. It generates images and compare the signature with expected ones. Maybe some test fails due metadata of some generated images, but at the moment I haven't any reported issue.
I created a test google account to perform the testing and put read-only credentials in this repository publicly. It is not a good security practice but I'm going to trust in people of doesn't doing something nasty.

## License
MIT license. Use it as you want at your own risk.

## About language
I'm not a native english writer, so there may be a lot of grammar and orthographical errors on text, I'm just trying my best. But feel free to correct my language, any contribution is welcome and for me they are a learning instance.


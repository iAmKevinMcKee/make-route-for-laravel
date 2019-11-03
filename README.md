# Make Route For Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/intellow/make-route-for-laravel.svg?style=flat-square)](https://packagist.org/packages/intellow/make-route-for-laravel)
[![Build Status](https://img.shields.io/travis/intellow/make-route-for-laravel/master.svg?style=flat-square)](https://travis-ci.org/intellow/make-route-for-laravel)
[![Quality Score](https://img.shields.io/scrutinizer/g/intellow/make-route-for-laravel.svg?style=flat-square)](https://scrutinizer-ci.com/g/intellow/make-route-for-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/intellow/make-route-for-laravel.svg?style=flat-square)](https://packagist.org/packages/intellow/make-route-for-laravel)

This is an opinionated package that creates boilerplate in your routes and controller files.

## Installation

You can install the package via composer:

```bash
composer require intellow/make-route-for-laravel
```

## Usage

In your command line, you can now use a single artisan command to create the following:
- Entry in your routes file (web.php)
- Controller created if one does not exist
- Your specified method added to the bottom of the controller
- An empty view is created
- For index and create actions, a basic unit test is created
- More to come in future releases

``` php
php artisan make:model-route Model resourcefulAction
```

So if you run the following command
``` php
php artisan make:model PizzaPie index
```

You will get the following:

``` php
// in web.php
Route::get('/pizza-pies/', [\App\Http\Controllers\PizzaPieController::class, 'index']);

// in Http\Controllers\PizzaPieController.php
// This file will be created if it doesn't exist already
// The following method will be appended to the end of the file

/**
 * Display a listing of the resource.
 *
 * @param Request $request
 */
public function index()
{
    //
}

```

### Security

If you discover any security related issues, please email kevin@intellow.com instead of using the issue tracker.

## Credits

- [Kevin McKee](https://github.com/intellow)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).

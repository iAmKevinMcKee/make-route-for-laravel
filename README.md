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
- Model is created if one doesn't already exist (optional)
- Migration for the model is created if one doesn't already exist (optional)
- An empty view is created
- For index and create actions, a basic unit test is created
- More to come in future releases


## Model Routes
If you are creating a route associated with a model, this is a great way to scaffold everything you need for that route.
``` php
php artisan make:model-route Model resourcefulAction
```

So if you run the following command
``` php
php artisan make:model-route PizzaPie index
```

You will get the following:

#### web.php
``` php
Route::get('/pizza-pies/', [\App\Http\Controllers\PizzaPieController::class, 'index']);
```
#### Http\Controllers\PizzaPieController.php
- This file will be created if it doesn't exist already
- The method will be added to the bottom of the controller
``` php
public function index()
{
    return view('models.pizza_pie.index');
}
```
#### resources\views\models\pizza_pie\index.blade.php
- Create this directory if it doesn't exist already
- Add a blank file with the following comment
``` html
{{--Create Something Amazing--}}
```
#### tests/Feature/AutomatedRouteTests.php
- This is only done on index and create actions
- Create this file if it doesn't already exist
- A basic feature test to hit this route (GET only) and assert a successful response
``` php
public function testPizzaPieIndex()
{
    $response = $this->get('pizza-pies');

    $response->assertStatus(200);
}
```
#### Create Model and Migration
- If you have not yet created the model, you will be given the option to create it. If you choose to create the model, you can also choose to create the migration as well.
- This package will run `php artisan make:model PizzaPie`  or `php artisan make:model PizzaPie -m` based on your choices

## Non-Model Routes
If you are creating a route that is not associated with a model, the package works a bit differently.
``` php
php artisan make:route <slug> <resourceful-action> [<controller-name>]
```

So if you run the following command
``` php
php artisan make:route /send-activation-email store
```

You will get the following:

#### web.php
``` php
Route::post('/send-activation-email/', [\App\Http\Controllers\SendActivationEmailController::class, 'store']);
```
#### Http\Controllers\SendActivationEmailController.php
- This file will be created if it doesn't exist already
- The method will be added to the bottom of the controller
``` php
public function store(Request $request)
{
    
}
```
No view is created because this is a store action, and no test is created either since it is not an index or create action.

### Security

If you discover any security related issues, please email kevin@intellow.com instead of using the issue tracker.

## Credits

- [Kevin McKee](https://github.com/intellow)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).

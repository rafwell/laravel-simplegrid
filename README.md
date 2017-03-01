##About this project
**rafwell/laravel-simplegrid** is a componente for build powerfull grids, with less code. The component is ready to work with Bootstrap 3, have features to export to xls/csv, simple/advanced search, ordenation, actions inline or bulk.

##Compatibility
**rafwell/laravel-simplegrid** is compatibly with Laravel 5.2+

##Instalation
1. Add the dependency to your composer.json ```composer require "rafwell/laravel-simplegrid"``` or ```"rafwell/laravel-simplegrid": "~0.0"```.
2. Execute ```composer update```.
3. Add to you ```app/config/app.php``` our service provider:
```@php
Rafwell\Simplegrid\SimplegridServiceProvider::class
```
4. Execute ```php artisan vendor:publish --provider="Rafwell\Simplegrid\SimplegridServiceProvider"```
5. Include in your html the js and css dependencies.

###Dependencies
This package will writen to work with bootstrap 3 and Jquery. We need the following dependencies:

* [Datetimepicker](https://eonasdan.github.io/bootstrap-datetimepicker/), for advanced search in date and datetime fields.
* [Moment]https://github.com/moment/moment), for Datetimepicker work.

Properly we added to our package those dependencies. You can add this from your ```public/vendor/rafwell/simplegrid```, like that:
####JS Files

```@html
<!-- ONLY INCLUDE IF YOU NOT HAVE THOSE DEPENDENCIES -->
<script src="vendor/rafwell/simplegrid/moment/moment.js"></script>
<script type="text/javascript" src="vendor/rafwell/simplegrid/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>

<!-- JS LARAVELSIMPLEGRID -->
<script src="vendor/rafwell/simplegrid/js/simplegrid.js"></script>
```
####CSS Files
```
<!-- S ONLY INCLUDE IF YOU NOT HAVE THOSE DEPENDENCIES -->
<link rel="stylesheet" href="vendor/rafwell/simplegrid/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />

<!-- CSS LARAVEL-GRID -->
<link rel="stylesheet" href="vendor/rafwell/simplegrid/css/simplegrid.css">
```

##An simple example

In your controller:
```@php
use Rafwell\Simplegrid\Grid;
```
In your function:
```@php
$Grid = new Grid(Employe::query(), 'Employes');
    	
$Grid->fields([
  'birth_date'=>'Birthday',
  'first_name'=>'First Name',
  'last_name'=>'Last Name',
  'gender'=>[
          'label'=>'Gender',
          'field'=>"case when gender = 'M' then 'Male' else 'Female' end"
      ]
]);
return view('yourview', ['grid'=>$Grid]);
```
In your view:
```@php
{!!$grid->make()!!}
```

##Disclaimer
This repository is new, 'forked' from [rafwell/laravel-grid](https://github.com/rafwell/laravel-grid). The original repository does not contemple multi-language features. Him will'be descontinued when this is ready for production applications.

##Contribute
If you want contribute, you can open issues to discussion.

##License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

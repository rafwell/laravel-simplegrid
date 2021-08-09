## About this project
**rafwell/laravel-simplegrid** is a component for build powerful grids, with less code. The component is ready to work with Bootstrap 3, have features to export to xls, csv and pdf, simple/advanced search, ordenation, actions inline or bulk.

## Compatibility
**rafwell/laravel-simplegrid** is compatibly with Laravel 5.2+

## Instalation
1. Add the dependency to your composer.json ```composer require "rafwell/laravel-simplegrid"``` or ```"rafwell/laravel-simplegrid": "^2.0"```.
2. Execute ```composer update```.
3. Add to your ```config/app.php``` our service provider: ```Rafwell\Simplegrid\SimplegridServiceProvider::class```
4. Execute ```php artisan vendor:publish --provider="Rafwell\Simplegrid\SimplegridServiceProvider"```
5. Include in your html the js and css dependencies.

### Dependencies
This package was written to work with bootstrap 3 and Jquery. We need  the following dependencies:

* [Datetimepicker](https://eonasdan.github.io/bootstrap-datetimepicker/), for advanced search in date and datetime fields.
* [Moment](https://github.com/moment/moment), for Datetimepicker work.

Properly we added to our package those dependencies. You can add this from your ```public/vendor/rafwell/simple-grid```, like that:

#### CSS Files
```html
<!-- ONLY INCLUDE IF YOU NOT HAVE THOSE DEPENDENCIES -->
<link rel="stylesheet" href="vendor/rafwell/simple-grid/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />

<!-- CSS LARAVEL SIMPLEGRID -->
<link rel="stylesheet" href="vendor/rafwell/simple-grid/css/simplegrid.css">
```

#### JS Files
```html
<!-- ONLY INCLUDE IF YOU NOT HAVE THOSE DEPENDENCIES -->
<script src="vendor/rafwell/simple-grid/moment/moment.js"></script>
<script type="text/javascript" src="vendor/rafwell/simple-grid/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>

<!-- JS LARAVEL SIMPLEGRID -->
<script src="vendor/rafwell/simple-grid/js/simplegrid.js"></script>
```

## An simple example

In your controller:
```php
use Rafwell\Simplegrid\Grid;
```
In your function:
```php
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
```php
{!!$grid->make()!!}
```
The result will be like this:
![Simple grid](http://i.imgur.com/X5idnfi.png)

## A more complex example
Change the code of your controller to:
```php
$Grid->fields([
    'birth_date'=>'Birthday',
    'first_name'=>'First Name',
    'last_name'=>'Last Name',
    'gender'=>[
        'label'=>'Gender',
        'field'=>"case when gender = 'M' then 'Male' else 'Female' end"
    ]
])
->actionFields([
    'emp_no' //The fields used for process actions. those not are showed 
])
->advancedSearch([
    'birth_date'=>['type'=>'date','label'=>'Birthday'],
    'first_name'=>'First Name', // It's a shortcut for ['type'=>'text', 'label'=>'First Name'],
    'last_name'=>[
        //omiting the label. I'ts a shortcut, like above
        'type'=>'text',
        'sanitize'=>false //This field will not be sanitized
    ],
    'gender'=>[
        'type'=>'select',
        'label'=>'Gender',
        'options'=>['Male'=>'Male', 'Female'=>'Female'] //The key is the value of option
    ]
]);

$Grid->action('Edit', 'test/edit/{emp_no}')
->action('Delete', 'test/{emp_no}', [
    'confirm'=>'Do you with so continue?',
    'method'=>'DELETE',
]);

$Grid->checkbox(true, 'emp_no');
$Grid->bulkAction('Delete selected itens', '/test/bulk-delete');
```
The result will be like this:
![Complex grid](https://image.ibb.co/jyi4aa/Captura_de_tela_de_2017_03_01_15_12_05.png)
The advanced search allow you search field by field. The rendered is like this:
![Complex grid advanced search](https://image.ibb.co/mvESva/Captura_de_tela_de_2017_03_01_15_14_03.png)

#### Your model has relationships? Try it:
```php
//Make your query using eloquent orm normally
$Employe = Employe::join('supervisors', 'supervisors.id','=','employees.supervisor_id');

$Grid = new Grid($Employe, 'Employes');

//Here, the key or array of fields is the name of the field. so, you can concatenate with the table name
//You can make sub queries too
$Grid->fields([
    'birth_date'=>'Birthday', //If you not explicit the name of table, we use the principal table of query builded. in this case, employees's
    'first_name'=>'First Name',
    'last_name'=>'Last Name', 
    'gender'=>[
        'label'=>'Gender',
        'field'=>"case when gender = 'M' then 'Male' else 'Female' end" //This is a calculated field too
    ],
    'supervisors.name'=>'Supervisor Name', //There the example with relationship
    'virtual_field'=>[
        'label'=>'My first virtual field',
        'field'=>'(select column from table where...)' //Obviously, this subquery must return only 1 row
    ]
]);
//Continue code...
//Easier than that? :)
```
#### Mutators
All mutators *get* of the principal table will work normally when the renderer called. For personalize or trait an line before show, for make an visual concatenate or formatter, you can use the ```processLine``` method:
```php
$Grid->fields([
  'birth_date'=>'Birthday',
  'first_name'=>'First Name',
  'last_name'=>'Last Name',
  'gender'=>'Gender'
])
->processLine(function($row){
    //This function will be called for each row
    $row['gender'] = $row['gender'] == 'M' ? 'Male' : 'Female';
    //Do more you need on this row
    return $row; //Do not forget to return the row
});
```

In some case, some actions cannot be called. For example, if you cannot allow edit button if the status is equal 2:
```php
$Grid->fields([
  'birth_date'=>'Birthday',
  'first_name'=>'First Name',
  'last_name'=>'Last Name',
  'gender'=>'Gender',
  'status'=>'Status'//It's a integer on database. If 2 not allowed edit
])
>action('Edit', 'test/edit/{emp_no}')
->processLine(function($row){
    //This function will be called for each row
    if($row['status']==2)
        unset($row['gridActions']['edit']);
    //Do more you need on this row
    return $row; //Do not forget to return the row
});
//Awesome!
```

## Extra Configurations
After you publish the our service provider, will apear in your config folder a file named ```rafwell-simplegrid.php```. In him, you can change the standard of date and datetime fields of advanced search, the initial value of 'showing x rows per page' and more!

## Language
This package is multi-language. We use the location of you laravel instalattion, configured in your ```config/app.php```. If we have the translation this will be automatically loaded. 
You can see the currently supported languages in our [lang folder](resources/lang).

## Disclaimer
This repository is new, 'forked' from [rafwell/laravel-grid](https://github.com/rafwell/laravel-grid). The original repository does not contemple multi-language features. It has been discontinued in favor of this.

## Contribute
If you want contribute, you can open issues to discussion.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Breakingchanges log
* Since the version 2.0 we have added an [html-sanitizer](https://github.com/tgalopin/html-sanitizer) by default. This can break your code if you are showing some specific html entities in your grid. Note thatn on the version 1 was your responsability prevent that. If you are not sanitizing your data before renderi it, they can be unsafe, you can be vulnerable to attacks XSS. We highly recommend all you to update to the version 2.0, the default tags allowed is very common, rarely will affect your code. 
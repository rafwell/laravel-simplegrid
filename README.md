## About this project

**rafwell/laravel-simplegrid** is a component for building powerful grids with less code. It is ready to work with Bootstrap 3 and includes export to XLS, CSV and PDF, simple/advanced search, sorting, and inline or bulk actions.

## Compatibility

**rafwell/laravel-simplegrid** is compatible with Laravel 5.2+.

## Installation

1. Add the dependency to your composer.json: `composer require "rafwell/laravel-simplegrid"` or `"rafwell/laravel-simplegrid": "^2.0"`.
2. Run `composer update`.
3. Add our service provider to `config/app.php`: `Rafwell\Simplegrid\SimplegridServiceProvider::class`
4. Run `php artisan vendor:publish --provider="Rafwell\Simplegrid\SimplegridServiceProvider"`
5. Include the JS and CSS dependencies in your HTML.

### Dependencies

This package was written to work with Bootstrap 3 and jQuery. You need the following dependencies:

- [Datetimepicker](https://eonasdan.github.io/bootstrap-datetimepicker/), for advanced search on date and datetime fields.
- [Moment](https://github.com/moment/moment), required by Datetimepicker.

We ship those dependencies with the package. You can include them from `public/vendor/rafwell/simple-grid`, like this:

#### CSS Files

```html
<!-- ONLY INCLUDE IF YOU DO NOT ALREADY HAVE THESE DEPENDENCIES -->
<link
  rel="stylesheet"
  href="vendor/rafwell/simple-grid/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css"
/>

<!-- CSS LARAVEL SIMPLEGRID -->
<link rel="stylesheet" href="vendor/rafwell/simple-grid/css/simplegrid.css" />
```

#### JS Files

```html
<!-- ONLY INCLUDE IF YOU DO NOT ALREADY HAVE THESE DEPENDENCIES -->
<script src="vendor/rafwell/simple-grid/moment/moment.js"></script>
<script
  type="text/javascript"
  src="vendor/rafwell/simple-grid/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"
></script>

<!-- JS LARAVEL SIMPLEGRID -->
<script src="vendor/rafwell/simple-grid/js/simplegrid.js"></script>
```

## A simple example

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

The result will look like this:
![Simple grid](http://i.imgur.com/X5idnfi.png)

## Blade component and slots

When the page has extra filters and/or a create button, do **not** call `make()` in the controller. Pass the `Grid` instance and render `<x-simplegrid>` so those fields share the grid GET form.

Controller:

```php
return view('yourview', [
    'grid' => $Grid, // Grid instance, not $Grid->make()
]);
```

View:

```blade
<x-simplegrid :grid="$grid">
    <x-slot:extra-search>
        {{-- Rendered at the top of the grid GET form. Submitting extra filters or the grid search sends both. --}}
        <input type="text" name="filtro[data_ini]" value="{{ $filtro['data_ini'] ?? '' }}">
        <input type="text" name="filtro[data_fim]" value="{{ $filtro['data_fim'] ?? '' }}">
        <button type="submit" class="btn btn-primary">Filter</button>
    </x-slot:extra-search>

    <x-slot:header-actions>
        {{-- Rendered on the right of the simple/advanced search bar (flex, space-between). --}}
        <a href="admin/employes/create" class="btn btn-primary">
            <span class="fa fa-plus"></span> New
        </a>
    </x-slot:header-actions>
</x-simplegrid>
```

### Slots

| Slot             | Where it renders                                    | Typical content                                                                                                                                     |
| ---------------- | --------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| `extra-search`   | Inside the grid GET form, above the search bar      | Page-level filters. Give inputs a `name` so they submit with the grid search (e.g. `filtro[data_ini]`). Use `type="submit"` for the filter button.  |
| `header-actions` | Same row as the simple search, aligned to the right | Create/new links. Named `header-actions` so it does not collide with row `$actions`. Do not use `pull-right`; alignment is handled by the grid CSS. |

Both slots are optional. `{!!$grid->make()!!}` still works for grids with no extra UI.

### Query string

Extra fields with `name="filtro[...]"` are sent as nested arrays. Pagination, sorting and export keep them via `http_build_query`. A JSON string `?filtro={...}` is still accepted if the application decodes it.

Do not put extra filters only outside the grid form (a second search that does not submit `search`). The slots exist so one submit sends extra filters **and** the grid search.

## A more complex example

Change the code in your controller to:

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
    'emp_no' // Fields used to process actions. These are not shown.
])
->advancedSearch([
    'birth_date'=>['type'=>'date','label'=>'Birthday'],
    'first_name'=>'First Name', // Shortcut for ['type'=>'text', 'label'=>'First Name']
    'last_name'=>[
        // Omitting the label. Same shortcut as above.
        'type'=>'text',
        'sanitize'=>false // This field will not be sanitized
    ],
    'gender'=>[
        'type'=>'select',
        'label'=>'Gender',
        'options'=>['Male'=>'Male', 'Female'=>'Female'] // The key is the option value
    ]
]);

$Grid->action('Edit', 'test/edit/{emp_no}')
->action('Delete', 'test/{emp_no}', [
    'confirm'=>'Do you want to continue?',
    'method'=>'DELETE',
]);

$Grid->checkbox(true, 'emp_no');
$Grid->bulkAction('Delete selected items', '/test/bulk-delete');
```

The result will look like this:
![Complex grid](https://image.ibb.co/jyi4aa/Captura_de_tela_de_2017_03_01_15_12_05.png)
Advanced search lets you search field by field. It looks like this:
![Complex grid advanced search](https://image.ibb.co/mvESva/Captura_de_tela_de_2017_03_01_15_14_03.png)

#### Your model has relationships? Try this:

```php
// Build your query with Eloquent as usual
$Employe = Employe::join('supervisors', 'supervisors.id','=','employees.supervisor_id');

$Grid = new Grid($Employe, 'Employes');

// The field key is the field name, so you can prefix it with the table name.
// You can use subqueries too.
$Grid->fields([
    'birth_date'=>'Birthday', // If you do not set the table name, we use the main table of the query. In this case, employees.
    'first_name'=>'First Name',
    'last_name'=>'Last Name',
    'gender'=>[
        'label'=>'Gender',
        'field'=>"case when gender = 'M' then 'Male' else 'Female' end" // Calculated field
    ],
    'supervisors.name'=>'Supervisor Name', // Relationship example
    'virtual_field'=>[
        'label'=>'My first virtual field',
        'field'=>'(select column from table where...)' // This subquery must return only 1 row
    ]
]);
// Continue as usual...
```

#### Mutators

Getter mutators on the main table work as usual when the row is rendered. To customize a row before it is shown (concatenate values, format, etc.), use the `processLine` method:

```php
$Grid->fields([
  'birth_date'=>'Birthday',
  'first_name'=>'First Name',
  'last_name'=>'Last Name',
  'gender'=>'Gender'
])
->processLine(function($row){
    // This function is called for each row
    $row['gender'] = $row['gender'] == 'M' ? 'Male' : 'Female';
    // Do whatever else you need on this row
    return $row; // Do not forget to return the row
});
```

In some cases an action should not be shown. For example, hide the edit button when status is 2:

```php
$Grid->fields([
  'birth_date'=>'Birthday',
  'first_name'=>'First Name',
  'last_name'=>'Last Name',
  'gender'=>'Gender',
  'status'=>'Status' // Integer in the database. If 2, edit is not allowed
])
->action('Edit', 'test/edit/{emp_no}')
->processLine(function($row){
    // This function is called for each row
    if($row['status']==2)
        unset($row['gridActions']['edit']);
    // Do whatever else you need on this row
    return $row; // Do not forget to return the row
});
```

## Extra configuration

After you publish the service provider, a file named `rafwell-simplegrid.php` will appear in your config folder. There you can change the date and datetime formats for advanced search, the default "showing x rows per page" value, and more.

## Language

This package is multilingual. We use the locale of your Laravel installation, configured in `config/app.php`. If a translation exists, it is loaded automatically.
You can see the currently supported languages in our [lang folder](resources/lang).

## Disclaimer

This repository was forked from [rafwell/laravel-grid](https://github.com/rafwell/laravel-grid). The original repository does not include multilingual features and has been discontinued in favor of this one.

## Contribute

If you want to contribute, open an issue to start a discussion.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Changelog

- **3.5.0** — Extra search Blade slot (`<x-simplegrid>` + `extra-search`) so page-level filters submit with the grid form. Nested array query parameters are preserved as hidden fields. Header actions slot (`header-actions`) for create/new buttons on the same line as the simple search.
  — With `extra-search`, hidden fields keep only grid state (`grid`, `order`, `direction`, `page`, `rows-per-page`, `advanced-search`) so live filters are not duplicated in the query string. Form submit also disables hidden inputs that collide with visible fields. Submitting extra-search while advanced search is in use keeps the advanced panel open (and no longer crashes when `search` is an array).

## Breaking changes

- Since version 2.0 we include an [html-sanitizer](https://github.com/tgalopin/html-sanitizer) by default. This can break your code if you render specific HTML entities in the grid. In version 1 it was your responsibility to prevent that. If you were not sanitizing data before rendering, you could be vulnerable to XSS. We highly recommend upgrading to 2.0; the default allowed tags are common and will rarely affect your code.

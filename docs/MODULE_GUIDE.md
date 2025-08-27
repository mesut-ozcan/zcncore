# Module Guide

Each module contains:
- `module.json`
- `routes.php`
- optional: `Http/Controllers`, `Views`, `Migrations`, `Assets`, `lang`

Register routes in `routes.php`:
```php
$router = Core\\Application::get()->make('router');
$router->get('/example/{id}', [Modules\\Example\\Http\\Controllers\\ExampleController::class, 'show']);
return view('Example::detail', ['id'=>$id]);
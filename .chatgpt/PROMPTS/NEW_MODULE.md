You are adding a new ZCNCore module.

1. Create folder: /modules/{Name}/
2. Add module.json with name, slug, version, routes.php reference.
3. Create routes.php and register routes via Application::get()->make('router')->get(...)
4. Add controllers under Http/Controllers and views under Views/
5. Optionally add Migrations/* and Assets/*

Return complete file contents for each file you introduce.

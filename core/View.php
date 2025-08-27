<?php
namespace Core;

class View
{
    public static function render(string $view, array $data = []): string
    {
        $paths = self::resolvePaths($view);
        extract($data, EXTR_SKIP);
        ob_start();
        $file = $paths['resolved'];
        include $file;
        return ob_get_clean();
    }

    private static function resolvePaths(string $view): array
    {
        $app = Application::get();
        $theme = Config::get('app.theme', 'default');

        $candidates = [
            $app->basePath("themes/$theme/views/$view.php"),
            $app->basePath("app/Views/$view.php"),
        ];

        // Module view candidate: allow "Module::viewname"
        if (strpos($view, '::') !== false) {
            [$module, $name] = explode('::', $view, 2);
            array_splice($candidates, 1, 0, [
                $app->basePath("themes/$theme/views/overrides/$module/$name.php"),
                $app->basePath("modules/$module/Views/$name.php"),
            ]);
        }

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return ['resolved' => $path];
            }
        }
        throw new \RuntimeException("View not found: $view");
    }
}

<?php
namespace Core;

final class View
{
    /**
     * Component çözümleme sırası:
     *  themes/<theme>/components/<name>.php
     *  app/Views/components/<name>.php
     */
    public static function component(string $name, array $data = []): string
    {
        $theme = (string) Config::get('app.theme', 'default');
        $name  = str_replace(['.', '/'], DIRECTORY_SEPARATOR, $name);
        $candidates = [
            base_path("themes/{$theme}/components/{$name}.php"),
            base_path("app/Views/components/{$name}.php"),
        ];
        foreach ($candidates as $file) {
            if (is_file($file)) {
                extract($data, EXTR_SKIP);
                ob_start();
                include $file;
                return ob_get_clean();
            }
        }
        return "<!-- component '{$name}' not found -->";
    }
}

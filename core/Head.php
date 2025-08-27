<?php
namespace Core;

final class Head
{
    private static array $state = [
        'title' => '',
        'description' => '',
        'canonical' => '',
        'robots' => 'index,follow',
        'meta' => [],       // name => content
        'props' => []       // property => content (og:*, twitter:*)
    ];

    public static function init(): void
    {
        self::$state = [
            'title' => '',
            'description' => '',
            'canonical' => '',
            'robots' => 'index,follow',
            'meta' => [],
            'props' => []
        ];
    }

    public static function setTitle(string $t){ self::$state['title']=$t; }
    public static function setDescription(string $d){ self::$state['description']=$d; }
    public static function setCanonical(string $c){ self::$state['canonical']=$c; }
    public static function setRobots(string $r){ self::$state['robots']=$r; }

    public static function addMeta(string $name, string $content){ self::$state['meta'][$name]=$content; }
    public static function addProperty(string $property, string $content){ self::$state['props'][$property]=$content; } // NEW

    public static function render(): string
    {
        $out = [];
        if (self::$state['title']) $out[] = '<title>'.e(self::$state['title']).'</title>';
        if (self::$state['description']) $out[] = '<meta name="description" content="'.e(self::$state['description']).'">';
        if (self::$state['canonical']) $out[] = '<link rel="canonical" href="'.e(self::$state['canonical']).'">';
        if (self::$state['robots']) $out[] = '<meta name="robots" content="'.e(self::$state['robots']).'">';
        foreach (self::$state['meta'] as $k=>$v) {
            $out[] = '<meta name="'.e($k).'" content="'.e($v).'">';
        }
        foreach (self::$state['props'] as $k=>$v) {
            $out[] = '<meta property="'.e($k).'" content="'.e($v).'">'; // OG/Twitter
        }
        return implode("\n", $out);
    }
}

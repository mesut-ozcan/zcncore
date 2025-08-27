<?php
namespace Core\SEO;

use Core\Response;
use Core\Request;

final class RedirectRegistry
{
    private static array $rules = []; // [ ['from'=>regex, 'to'=>string, 'code'=>301], ... ]

    public static function add(string $fromRegex, string $to, int $code = 301): void
    {
        self::$rules[] = ['from'=>$fromRegex, 'to'=>$to, 'code'=>$code];
    }

    public static function check(Request $req)
    {
        $path = $req->path();
        foreach (self::$rules as $r) {
            if (preg_match($r['from'], $path)) {
                return Response::redirect($r['to'], $r['code']);
            }
        }
        return null;
    }
}

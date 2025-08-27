<?php
namespace Core\Support;

final class Paginator
{
    /**
     * @return array{page:int, per:int, total:int, last:int, offset:int, limit:int, html:string}
     */
    public static function make(int $total, int $per, int $page, string $baseUrl, array $query = []): array
    {
        $per   = max(1, $per);
        $last  = max(1, (int)ceil($total / $per));
        $page  = min(max(1, $page), $last);
        $offset= ($page - 1) * $per;

        // html
        $html = '';
        if ($last > 1) {
            $html .= '<nav class="pagination">';
            $html .= self::link($baseUrl, $query, 1, 'İlk', $page <= 1);
            $html .= self::link($baseUrl, $query, max(1,$page-1), 'Önceki', $page <= 1);

            $start = max(1, $page - 2);
            $end   = min($last, $page + 2);
            for ($i=$start; $i<=$end; $i++) {
                $html .= self::link($baseUrl, $query, $i, (string)$i, $i === $page, true);
            }

            $html .= self::link($baseUrl, $query, min($last,$page+1), 'Sonraki', $page >= $last);
            $html .= self::link($baseUrl, $query, $last, 'Son', $page >= $last);
            $html .= '</nav>';
        }

        return [
            'page'=>$page, 'per'=>$per, 'total'=>$total, 'last'=>$last,
            'offset'=>$offset, 'limit'=>$per, 'html'=>$html
        ];
    }

    private static function link(string $baseUrl, array $query, int $page, string $label, bool $disabled=false, bool $active=false): string
    {
        $q = $query;
        $q['page'] = $page;
        $qs = http_build_query($q);
        $href = htmlspecialchars($baseUrl . (str_contains($baseUrl,'?') ? '&' : '?') . $qs, ENT_QUOTES, 'UTF-8');

        $cls = [];
        if ($disabled) $cls[] = 'disabled';
        if ($active)   $cls[] = 'active';
        $class = $cls ? ' class="'.implode(' ', $cls).'"' : '';

        if ($disabled && !$active) {
            return '<span'.$class.'>'.$label.'</span>';
        }
        return '<a href="'.$href.'"'.$class.'>'.$label.'</a>';
    }
}

<?php
namespace App\Http\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Support\Health;

class StatusController extends Controller
{
    public function index(Request $req): Response
    {
        $data = Health::summary();

        if ($req->wantsJson()) {
            return $this->json([
                'ok' => array_reduce($data, fn($c,$v)=>$c && ($v['ok']??false), true),
                'checks' => $data,
                'time' => date('c'),
            ]);
        }

        ob_start(); ?>
        <h1>Status</h1>
        <p><small><?= e(date('Y-m-d H:i:s')) ?></small></p>
        <table border="1" cellpadding="6" cellspacing="0">
            <thead><tr><th>Bileşen</th><th>Durum</th><th>Detay</th></tr></thead>
            <tbody>
            <?php foreach ($data as $k=>$v): ?>
                <tr>
                    <td><?= e($k) ?></td>
                    <td><?= !empty($v['ok']) ? 'OK' : 'FAIL' ?></td>
                    <td><?php
                        $d = $v; unset($d['ok']);
                        echo $d ? '<pre style="margin:0">'.e(var_export($d,true)).'</pre>' : '-';
                    ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return new Response(ob_get_clean());
    }
}

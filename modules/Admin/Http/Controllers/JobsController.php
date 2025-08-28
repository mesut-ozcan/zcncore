<?php
namespace Modules\Admin\Http\Controllers;

use Core\Request;
use Core\Response;
use Core\Queue\Queue;

final class JobsController
{
    public function failed(Request $req): Response
    {
        $jobs = Queue::failedJobs();
        usort($jobs, fn($a,$b)=>($b['createdAt'] <=> $a['createdAt']));

        ob_start();
        $data = ['jobs'=>$jobs];
        include base_path('modules/Admin/views/jobs_failed.php');
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    public function retry(Request $req): Response
    {
        // Intelephense P1013 kaçınmak için method() çağırma:
        $httpMethod = strtoupper($req->server['REQUEST_METHOD'] ?? 'GET');

        if ($httpMethod !== 'POST' || !\Core\Csrf::check($req->input['_token'] ?? '')) {
            return Response::redirect('/admin/jobs/failed');
        }

        $id = (string)($req->input['id'] ?? '');
        if ($id === '') {
            \Core\Session::flash('error', "Geçersiz job ID.");
            return Response::redirect('/admin/jobs/failed');
        }

        $ok = Queue::retryFailed($id);
        if ($ok) {
            \Core\Session::flash('success', "Job {$id} kuyruğa taşındı.");
        } else {
            \Core\Session::flash('error', "Job {$id} taşınamadı.");
        }
        return Response::redirect('/admin/jobs/failed');
    }

    public function delete(Request $req): Response
    {
        // Intelephense P1013 kaçınmak için method() çağırma:
        $httpMethod = strtoupper($req->server['REQUEST_METHOD'] ?? 'GET');

        if ($httpMethod !== 'POST' || !\Core\Csrf::check($req->input['_token'] ?? '')) {
            return Response::redirect('/admin/jobs/failed');
        }

        $id = (string)($req->input['id'] ?? '');
        if ($id === '') {
            \Core\Session::flash('error', "Geçersiz job ID.");
            return Response::redirect('/admin/jobs/failed');
        }

        $ok = Queue::deleteFailed($id);
        if ($ok) {
            \Core\Session::flash('success', "Job {$id} silindi.");
        } else {
            \Core\Session::flash('error', "Job {$id} silinemedi.");
        }
        return Response::redirect('/admin/jobs/failed');
    }
}
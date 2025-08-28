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

    public function detail(Request $req): Response
    {
        $id = (string)($req->query['id'] ?? '');
        $job = $id ? Queue::failedJob($id) : null;

        ob_start();
        $data = ['id'=>$id, 'job'=>$job];
        include base_path('modules/Admin/views/job_detail.php');
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    public function retry(Request $req): Response
    {
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
        \Core\Session::flash($ok ? 'success' : 'error', $ok ? "Job {$id} kuyruğa taşındı." : "Job {$id} taşınamadı.");
        return Response::redirect('/admin/jobs/failed');
    }

    public function delete(Request $req): Response
    {
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
        \Core\Session::flash($ok ? 'success' : 'error', $ok ? "Job {$id} silindi." : "Job {$id} silinemedi.");
        return Response::redirect('/admin/jobs/failed');
    }
}
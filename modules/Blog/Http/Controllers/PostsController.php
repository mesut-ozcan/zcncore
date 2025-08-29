<?php
namespace Modules\Blog\Http\Controllers;

use Core\Request;
use Core\Response;
use Core\Database\QueryBuilder as DB;
use PDOException;

final class PostsController
{
    /**
     * GET /blog
     */
    public function index(Request $req): Response
    {
        try {
            $posts = DB::table('posts')
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get();
        } catch (PDOException $e) {
            // tablo yoksa / bağlantı hatasıysa: boş liste göster
            $posts = [];
        }

        ob_start();
        $title = 'Blog';
        $items = $posts;
        $viewPath = base_path('modules/Blog/views/posts/index.php');
        if (is_file($viewPath)) {
            include $viewPath;
        } else {
            // simple fallback html
            echo "<h1>Blog</h1>";
            if (!$items) {
                echo "<p>Henüz içerik yok.</p>";
            }
            echo "<ul>";
            foreach ($items as $p) {
                $id = (int)($p['id'] ?? 0);
                $t  = htmlspecialchars($p['title'] ?? ('#'.$id), ENT_QUOTES, 'UTF-8');
                echo "<li><a href=\"/blog/{$id}\">{$t}</a></li>";
            }
            echo "</ul>";
        }
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    /**
     * GET /blog/{id}
     */
    public function show(Request $req, int $id): Response
    {
        try {
            $post = DB::table('posts')->where('id', '=', $id)->first();
        } catch (PDOException $e) {
            $post = null;
        }

        if (!$post) {
            return new Response('<h1>404 Not Found</h1>', 404, ['Content-Type'=>'text/html; charset=UTF-8']);
        }

        ob_start();
        $title = $post['title'] ?? 'Post';
        $item  = $post;
        $viewPath = base_path('modules/Blog/views/posts/show.php');
        if (is_file($viewPath)) {
            include $viewPath;
        } else {
            echo "<h1>".htmlspecialchars($item['title'] ?? '', ENT_QUOTES, 'UTF-8')."</h1>";
            echo "<div>".nl2br(htmlspecialchars($item['content'] ?? '', ENT_QUOTES, 'UTF-8'))."</div>";
        }
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    /**
     * GET /blog/create
     */
    public function create(Request $req): Response
    {
        ob_start();
        $title = 'Yeni Yazı';
        $viewPath = base_path('modules/Blog/views/posts/create.php');
        if (is_file($viewPath)) {
            include $viewPath;
        } else {
            echo '<h1>Yeni Yazı</h1>';
            echo '<form method="post" action="/blog">';
            echo csrf_field();
            echo '<p><input type="text" name="title" placeholder="Başlık"></p>';
            echo '<p><textarea name="content" placeholder="İçerik"></textarea></p>';
            echo '<p><button type="submit">Kaydet</button></p>';
            echo '</form>';
        }
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    /**
     * POST /blog
     */
    public function store(Request $req): Response
    {
        $data = $req->validate([
            'title'   => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2',
        ]);

        try {
            DB::table('posts')->insert([
                'title'      => $data['title'],
                'content'    => $data['content'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            flash('success', 'Kayıt eklendi.');
        } catch (PDOException $e) {
            flash('error', 'Kayıt eklenemedi. (Tablo yok mu?)');
        }

        return Response::redirect('/blog', 302);
    }

    /**
     * GET /blog/{id}/edit
     */
    public function edit(Request $req, int $id): Response
    {
        try {
            $post = DB::table('posts')->where('id', '=', $id)->first();
        } catch (PDOException $e) {
            $post = null;
        }

        if (!$post) {
            return new Response('<h1>404 Not Found</h1>', 404, ['Content-Type'=>'text/html; charset=UTF-8']);
        }

        ob_start();
        $title = 'Yazıyı Düzenle';
        $item  = $post;
        $viewPath = base_path('modules/Blog/views/posts/edit.php');
        if (is_file($viewPath)) {
            include $viewPath;
        } else {
            echo '<h1>Yazıyı Düzenle</h1>';
            echo '<form method="post" action="/blog/'.$id.'/update">';
            echo csrf_field();
            echo '<p><input type="text" name="title" value="'.e($item['title']).'"></p>';
            echo '<p><textarea name="content">'.e($item['content']).'</textarea></p>';
            echo '<p><button type="submit">Güncelle</button></p>';
            echo '</form>';
        }
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    /**
     * POST /blog/{id}/update
     */
    public function update(Request $req, int $id): Response
    {
        $data = $req->validate([
            'title'   => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2',
        ]);

        try {
            $ok = DB::table('posts')->where('id', '=', $id)->update([
                'title'      => $data['title'],
                'content'    => $data['content'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (PDOException $e) {
            $ok = false;
        }

        if (!$ok) {
            flash('error', 'Güncelleme başarısız. (Tablo yok mu?)');
            return Response::redirect('/blog/'.$id.'/edit', 302);
        }

        flash('success', 'Güncellendi.');
        return Response::redirect('/blog/'.$id, 302);
    }

    /**
     * POST /blog/{id}/delete
     */
    public function destroy(Request $req, int $id): Response
    {
        try {
            DB::table('posts')->where('id', '=', $id)->delete();
            flash('success', 'Silindi.');
        } catch (PDOException $e) {
            flash('error', 'Silinemedi. (Tablo yok mu?)');
        }
        return Response::redirect('/blog', 302);
    }
}
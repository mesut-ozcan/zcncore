<?php
namespace Modules\Blog\Http\Controllers;

use Core\Request;
use Core\Response;
use Core\Database\QueryBuilder as DB;
use PDOException;

final class PostsController
{
    /** GET /blog */
    public function index(Request $req): Response
    {
        try {
            $posts = DB::table('posts')
                ->orderBy('published_at','DESC')
                ->orderBy('id','DESC')
                ->limit(20)
                ->get();
        } catch (PDOException $e) {
            // tablo migrate edilmemişse boş liste ile render
            $posts = [];
        }

        ob_start();
        $title = 'Blog';
        $items = $posts;
        $viewPath = base_path('modules/Blog/views/posts/index.php');
        if (is_file($viewPath)) {
            include $viewPath; // ✅ parantez fazlası kaldırıldı
        } else {
            echo "<h1>Blog</h1>";
            echo "<ul>";
            foreach ($items as $p) {
                $t = htmlspecialchars($p['title'] ?? '', ENT_QUOTES, 'UTF-8');
                $s = htmlspecialchars($p['slug'] ?? '', ENT_QUOTES, 'UTF-8');
                echo "<li><a href=\"/blog/{$s}\">{$t}</a></li>";
            }
            echo "</ul>";
        }
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    /** GET /blog/{slug} */
    public function show(Request $req, string $slug): Response
    {
        $post = DB::table('posts')->where('slug', '=', $slug)->first();
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

    /** GET /blog/create */
    public function create(Request $req): Response
    {
        ob_start();
        $title = 'Yeni Yazı';
        $viewPath = base_path('modules/Blog/views/posts/create.php');
        if (is_file($viewPath)) {
            include $viewPath; // ✅
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

    /** POST /blog */
    public function store(Request $req): Response
    {
        $data = $req->validate([
            'title'   => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2',
        ]);

        // slug üret
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $data['title']), '-'));

        DB::table('posts')->insert([
            'title'        => $data['title'],
            'slug'         => $slug,
            'content'      => $data['content'],
            'created_at'   => date('Y-m-d H:i:s'),
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        flash('success', 'Kayıt eklendi.');
        return Response::redirect('/blog/'.$slug, 302);
    }

    /** GET /blog/{slug}/edit */
    public function edit(Request $req, string $slug): Response
    {
        $post = DB::table('posts')->where('slug', '=', $slug)->first();
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
            echo '<form method="post" action="/blog/'.$slug.'/update">';
            echo csrf_field();
            echo '<p><input type="text" name="title" value="'.e($item['title']).'"></p>';
            echo '<p><textarea name="content">'.e($item['content']).'</textarea></p>';
            echo '<p><button type="submit">Güncelle</button></p>';
            echo '</form>';
        }
        return new Response(ob_get_clean(), 200, ['Content-Type'=>'text/html; charset=UTF-8']);
    }

    /** POST /blog/{slug}/update */
    public function update(Request $req, string $slug): Response
    {
        $data = $req->validate([
            'title'   => 'required|string|min:2|max:255',
            'content' => 'required|string|min:2',
        ]);

        // başlık değişmişse slug da güncelle
        $newSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i','-', $data['title']), '-'));

        $ok = DB::table('posts')->where('slug', '=', $slug)->update([
            'title'      => $data['title'],
            'slug'       => $newSlug,
            'content'    => $data['content'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            flash('error', 'Güncelleme başarısız.');
            return Response::redirect('/blog/'.$slug.'/edit', 302);
        }

        flash('success', 'Güncellendi.');
        return Response::redirect('/blog/'.$newSlug, 302);
    }

    /** POST /blog/{slug}/delete */
    public function destroy(Request $req, string $slug): Response
    {
        DB::table('posts')->where('slug', '=', $slug)->delete();
        flash('success', 'Silindi.');
        return Response::redirect('/blog', 302);
    }
}
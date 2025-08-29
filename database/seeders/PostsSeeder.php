<?php
use Core\Database\Connection;

return new class {
    public function run(): void {
        $pdo = Connection::getInstance()->pdo();
        $now = date('Y-m-d H:i:s');

        $rows = [
            ['title'=>'ZCNCore nedir?', 'slug'=>'zcncore-nedir', 'body'=>"Merhaba dünya!\nZCNCore ile ilk yazınız.", 'published_at'=>$now],
            ['title'=>'Taslak bir yazı',  'slug'=>'taslak-bir-yazi', 'body'=>"Bu bir taslaktır.", 'published_at'=>null],
        ];

        $stmt = $pdo->prepare("INSERT INTO posts (title,slug,body,published_at,created_at,updated_at) VALUES (:t,:s,:b,:p,:c,:u)");
        foreach ($rows as $r) {
            $stmt->execute([
                ':t'=>$r['title'], ':s'=>$r['slug'], ':b'=>$r['body'],
                ':p'=>$r['published_at'], ':c'=>$now, ':u'=>$now
            ]);
        }
    }
};

<?php
namespace Core\Support;

final class Upload
{
    public static function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^\w.\-]+/u', '_', $name);
        $name = trim($name, '._');
        return $name ?: 'file';
    }

    /** @return array{ok:bool, error?:string} */
    public static function validate(array $file, array $cfg): array
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['ok'=>false, 'error'=>'Yükleme hatası (UPLOAD_ERR)'];
        }
        if (!is_uploaded_file($file['tmp_name'] ?? '')) {
            return ['ok'=>false, 'error'=>'Geçersiz temp dosyası'];
        }
        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > (int)$cfg['max_bytes']) {
            return ['ok'=>false, 'error'=>'Dosya boyutu limit dışı'];
        }
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, $cfg['allowed_ext'], true)) {
            return ['ok'=>false, 'error'=>'Uzantıya izin verilmiyor'];
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) finfo_close($finfo);
        if ($mime === '' || !in_array($mime, $cfg['allowed_mime'], true)) {
            return ['ok'=>false, 'error'=>"MIME tipine izin verilmiyor ($mime)"];
        }

        // Görsel boyut doğrulaması
        if (isset($cfg['images']) && is_array($cfg['images']) && str_starts_with((string)$mime, 'image/')) {
            $info = @getimagesize($file['tmp_name']);
            if (!$info) {
                return ['ok'=>false, 'error'=>'Görsel okunamadı'];
            }
            [$w, $h] = $info;
            $mw = (int)($cfg['images']['max_width']  ?? 0);
            $mh = (int)($cfg['images']['max_height'] ?? 0);
            if (($mw && $w > $mw) || ($mh && $h > $mh)) {
                return ['ok'=>false, 'error'=>"Görsel boyutu çok büyük ({$w}x{$h}). Maks: {$mw}x{$mh}"];
            }
        }

        return ['ok'=>true];
    }

    /**
     * Dosyayı güvenli dizine taşır.
     * @return array{ok:bool, path?:string, url?:string, error?:string, name?:string}
     */
    public static function move(array $file, array $cfg): array
    {
        $base = rtrim($cfg['base_dir'], '/');
        $ym   = date('Y/m');
        $absDir = base_path("$base/$ym");
        if (!is_dir($absDir) && !@mkdir($absDir, 0777, true)) {
            return ['ok'=>false, 'error'=>'Dizin oluşturulamadı'];
        }

        $safe = self::sanitizeFilename($file['name'] ?? 'file');
        $ext  = strtolower(pathinfo($safe, PATHINFO_EXTENSION));
        $name = pathinfo($safe, PATHINFO_FILENAME);

        // Çakışmayı önle
        $candidate = $name . '.' . $ext;
        $i = 1;
        while (is_file("$absDir/$candidate")) {
            $candidate = $name . "_$i.$ext";
            $i++;
        }

        $dest = "$absDir/$candidate";
        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            return ['ok'=>false, 'error'=>'Dosya taşınamadı'];
        }

        return [
            'ok'   => true,
            'name' => $candidate,
            'path' => "$base/$ym/$candidate",
        ];
    }
}

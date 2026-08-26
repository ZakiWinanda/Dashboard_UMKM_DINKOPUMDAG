<?php
/**
 * Robust autoloader untuk PHPWord 0.13.0 (CI3 + PHP 5.6)
 * - Autoload namespace PhpOffice\...
 * - Fallback ke struktur lama PhpWord/...
 * - Jika masih gagal, scan folder src/ untuk mencari dan include file yang mendefinisikan class yang hilang
 */

if (!class_exists('PhpOffice\PhpWord\PhpWord', false)) {

    $baseDir = dirname(__FILE__) . '/src/';

    // 1) Autoloader utama untuk namespace modern
    spl_autoload_register(function ($class) use ($baseDir) {
        // hanya perhatian pada PhpOffice namespace
        if (strpos($class, 'PhpOffice\\') === 0) {
            $relativePath = str_replace('\\', '/', $class) . '.php';
            $pathModern = $baseDir . $relativePath;
            $pathLegacy = $baseDir . 'PhpWord/' . str_replace('PhpOffice/PhpWord/', '', $relativePath);

            if (file_exists($pathModern)) {
                require_once $pathModern;
                return true;
            }
            if (file_exists($pathLegacy)) {
                require_once $pathLegacy;
                return true;
            }
        }
        return false;
    });

    // 2) Quick manual includes untuk file-file yang sering hilang (cek dan include bila ada)
    $quickFiles = array(
        $baseDir . 'PhpWord/Common/Text.php',
        $baseDir . 'PhpWord/Common/XMLWriter.php',
        $baseDir . 'PhpWord/Common/Drawing.php',
        $baseDir . 'PhpWord/Collection/Bookmarks.php',
        $baseDir . 'PhpWord/Collection/Titles.php'
    );
    foreach ($quickFiles as $f) {
        if (file_exists($f)) {
            @require_once $f;
        }
    }

    // 3) Jika masih ada missing class runtime, lakukan scan untuk menemukan file yang mendefinisikannya.
    //    Fungsi helper: cari definisi class atau trait/namespace di file php.
    function phpword_scan_and_require($baseDir, $classNames = array()) {
        // ubah ke path absolut
        $baseDir = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR;
        if (!is_dir($baseDir)) return;

        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            if (strtolower($file->getExtension()) !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            // cepat cek apakah file menyebut class yang dicari
            foreach ($classNames as $cn) {
                // cari "class <Name" atau "namespace ...<Name" atau "interface <Name"
                if (preg_match('/\bclass\s+' . preg_quote($cn, '/') . '\b/i', $content) ||
                    preg_match('/\binterface\s+' . preg_quote($cn, '/') . '\b/i', $content) ||
                    preg_match('/\btrait\s+' . preg_quote($cn, '/') . '\b/i', $content)
                ) {
                    @require_once $file->getPathname();
                    // setelah require, check apakah class now exists; if so remove from list
                    foreach ($classNames as $k => $name) {
                        if (class_exists($name, false) || interface_exists($name, false) || trait_exists($name, false)) {
                            unset($classNames[$k]);
                        }
                    }
                    // if no more needed, return early
                    if (empty($classNames)) return;
                    // continue scanning to include others
                }
            }
        }
    }

    // Daftar kelas yang sering jadi masalah di v0.13.0 — tambahkan jika masih error muncul nama lain
    $possibleMissing = array(
        'Text', // fallback untuk class Text
        'Bookmarks',
        'XMLWriter',
        'Drawing',
        'File',
        'Settings'
    );

    // Selain nama pendek di atas, kita coba juga fullname namespace forms yang sering dipanggil:
    $possibleMissingNamespace = array(
        'PhpOffice\\Common\\Text',
        'PhpOffice\\PhpWord\\Collection\\Bookmarks',
        'PhpOffice\\PhpWord\\Shared\\XMLWriter',
        'PhpOffice\\PhpWord\\Shared\\Drawing',
        'PhpOffice\\PhpWord\\Settings'
    );

    // Jika class belum ada, scan dan require file yang mendefinisikannya.
    // Pertama scan menggunakan short names
    $missingToScan = array();
    foreach ($possibleMissing as $short) {
        if (!class_exists($short, false) && !class_exists('PhpOffice\\PhpWord\\' . $short, false) && !class_exists('PhpOffice\\Common\\' . $short, false)) {
            $missingToScan[] = $short;
        }
    }
    if (!empty($missingToScan)) {
        phpword_scan_and_require($baseDir, $missingToScan);
    }

    // lalu scan namespaces penuh (jika masih belum terdefinisi)
    $missingNsScan = array();
    foreach ($possibleMissingNamespace as $ns) {
        if (!class_exists($ns, false)) {
            // ambil basename sebagai fallback class name untuk scanning
            $parts = explode('\\', $ns);
            $missingNsScan[] = end($parts);
        }
    }
    if (!empty($missingNsScan)) {
        phpword_scan_and_require($baseDir, $missingNsScan);
    }

    // NOTE: jika masih muncul error dengan nama class lain, tambahkan nama class tersebut
    // pada array $possibleMissing atau $possibleMissingNamespace di atas.
}
?>

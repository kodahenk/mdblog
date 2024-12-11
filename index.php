<?php
require 'vendor/autoload.php';
use League\CommonMark\CommonMarkConverter;

function splitMarkdownSections($markdownContent)
{
    $lines = explode("\n", $markdownContent);
    $sections = [];
    $currentSection = "";

    foreach ($lines as $line) {
        if (preg_match('/^#+\s.+/', $line)) {
            // Yeni bir başlıksa mevcut bölümü kaydet ve yeni bölümü başlat
            if (!empty(trim($currentSection))) {
                $sections[] = trim($currentSection);
            }
            $currentSection = $line . "\n"; // Yeni başlıkla başla
        } else {
            // Başlık değilse mevcut bölüme ekle
            $currentSection .= $line . "\n";
        }
    }

    if (!empty(trim($currentSection))) {
        $sections[] = trim($currentSection);
    }

    return $sections;
}

function getAllMarkdownFiles($directory)
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $files = [];

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function getRandomMarkdownFile($directory)
{
    $files = getAllMarkdownFiles($directory);
    if (empty($files)) {
        return null;
    }
    return $files[array_rand($files)];
}

function getRandomMarkdownSection($directory)
{
    $file = getRandomMarkdownFile($directory);
    if ($file === null) {
        return ["Markdown Dosyası Bulunamadı", ""];
    }

    $markdownContent = file_get_contents($file);
    $sections = splitMarkdownSections($markdownContent);

    if (empty($sections)) {
        return ["Bölüm Bulunamadı", ""];
    }

    $randomSection = $sections[array_rand($sections)];
    return ["Başlık ve İçerik", $randomSection];
}

$directory = 'mdblog';
list($randomTitle, $randomContent) = getRandomMarkdownSection($directory);

$converter = new CommonMarkConverter();
$htmlContent = $converter->convert($randomContent);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Markdown Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Markdown Viewer</h1>
        <hr>
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h2><?php echo htmlspecialchars($randomTitle); ?></h2>
            </div>
            <div class="card-body">
                <div class="card-text">
                    <?php echo $htmlContent; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
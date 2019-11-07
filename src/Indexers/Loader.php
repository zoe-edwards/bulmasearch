<?php namespace ThomasEdwards\BulmaSearch\Indexers;

use ThomasEdwards\BulmaSearch\Objects\Objects;
use ThomasEdwards\BulmaSearch\Pages\Pages;

class Loader
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function load()
    {
        $files = $this->scan($this->path);
        $pages = (new Pages($files))->load();
        return (new Objects($pages))->build();
    }

    private function scan(string $dir, array $results = []): array
    {
        $files = scandir($dir);
        foreach ($files as $key => $file) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $file);
            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ($file !== '.' && $file !== '..') {
                $results = $this->scan($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }
}

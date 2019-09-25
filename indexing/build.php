<?php

require './vendor/autoload.php';

$algoliaClient = Algolia\AlgoliaSearch\SearchClient::create(
    env('ALGOLIA_APP_ID'),
    env('ALGOLIA_API_KEY'),
);

function scanDirectory($dir, &$results = array()) {

    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            scanDirectory($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

$files = scanDirectory('bulma/docs/documentation');
$objects = [];

foreach ($files as $file) {
    $page = new FrontMatter($file);
    if ($page->keyExists('title') && $page->keyExists('breadcrumb')) {
        list($filePath, $url) = explode('bulma/docs/', $file);
        $breadcrumb = $page->fetch('breadcrumb');
        unset($breadcrumb[0]);
        unset($breadcrumb[1]);
        $objects[] = [
            'objectID'   => basename($file, '.html'),
            'title'      => $page->fetch('title'),
            'url'        => str_replace('.html', '', $url),
            'breadcrumb' => implode(' > ', $breadcrumb),
        ];
    }
}

$index = $algoliaClient->initIndex('classes_holding');

$index->setSettings([
    'searchableAttributes' => ['title'],
    'customRanking' => ['asc(title)'],
]);

$index->saveObjects($objects);

$algoliaClient->moveIndex('classes_holding', 'classes');

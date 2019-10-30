<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/functions.php';

use Cocur\Slugify\Slugify;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->safeLoad();
$dotenv->required(['ALGOLIA_APP_ID', 'ALGOLIA_API_KEY']);

$algoliaClient = Algolia\AlgoliaSearch\SearchClient::create(
    getenv('ALGOLIA_APP_ID'),
    getenv('ALGOLIA_API_KEY'),
);

$slugify = new Slugify();

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
        $url = str_replace('.html', '', $url);
        $breadcrumb = cleanBreadcrumb($page->fetch('breadcrumb'));
        $contents = $page->fetch('content');
        $contents = cleanContent($contents);
        $contents = extractContent($contents);
        $contents = combineSections($contents);

        // If there's no content found, then it's probably a section overview page
        // Adding a blank section allows for the next loop to add the page
        if(count($contents) === 0) {
            $contents = [
                [
                    'section' => '',
                    'content' => ''
                ]
            ];
        }
        
        // Create objects from page content
        foreach($contents as $content) {
            $section = $content['section'];
            $objectId = $url;
            $sectionUrl = $url;
            $title = $page->fetch('title');
            $isRoot = empty($section);
            if(!$isRoot) {
                $sectionSlug = $slugify->slugify($section);
                $sectionUrl = $url . '#' . $sectionSlug;
                $objectId = $sectionUrl;
            }

            $breadcrumbLevel = count($breadcrumb);
            if($breadcrumbLevel === 1) {
                $fullTitle = [$title, $section];                
            } else {
                $fullTitle = [$breadcrumb[0], $title, $section];
            }
            $fullTitle = trim(implode(' ', $fullTitle));
            
            $objects[] = [
                'objectID' => $objectId,
                'pageUrl' => $url,
                'url' => $sectionUrl,
                'breadcrumbLevel' => $breadcrumbLevel,
                'pageRoot' => (int) $isRoot,
                'pageSection' => $section,
                'title' => $title,
                'breadcrumb' => implode(' > ', $breadcrumb),
                'fullTitle' => $fullTitle,
                'content' => $content['content']
            ];
        }
    }
}

$index = $algoliaClient->initIndex('classes_holding');

$index->setSettings([
    'searchableAttributes' => ['fullTitle', 'unordered(pageSection)', 'unordered(title)', 'unordered(content)'],
    'customRanking' => ['desc(pageRoot)', 'asc(pageSection)', 'asc(breadcrumbLevel)'],
    'attributeForDistinct' => 'pageUrl',
    'attributesToSnippet' => ['content'],
    'distinct' => 2,
    'ignorePlurals' => true
]);

$index->saveObjects($objects);

$algoliaClient->moveIndex('classes_holding', 'classes');

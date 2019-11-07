<?php namespace ThomasEdwards\BulmaSearch\Objects;

use Cocur\Slugify\Slugify;
use ThomasEdwards\BulmaSearch\Pages\Page;

/**
 * Class Converter
 *
 * Converts page sections into objects
 */
class Converter
{
    private $pages;
    private $objects = [];
    private $slugify;

    public function __construct(array $pages)
    {
        $this->pages = $pages;
        $this->slugify = new Slugify();
    }

    public function convert(): array
    {
        foreach ($this->pages as $page) {
            $this->convertPage($page);
        }

        return $this->objects;
    }

    private function convertPage($page)
    {
        foreach ($page['sections'] as $section) {
            list($sectionName, $sectionIsRoot) = $this->section($section['section']);
            $sectionUrl = $this->calculateSectionUrl($page['url'], $sectionName, $sectionIsRoot);
            $breadcrumbLevel = count($page['breadcrumb']);

            $this->objects[] = [
                'objectID' => $sectionUrl,
                'url' => $sectionUrl,
                'fullTitle' => $this->calculateFullTitle($page, $breadcrumbLevel, $sectionName),
                'pageUrl' => $page['url'],
                'pageTitle' => $page['title'],
                'pageBreadcrumb' => implode(' > ', $page['breadcrumb']),
                'pageBreadcrumbLevel' => $breadcrumbLevel,
                'sectionTitle' => $sectionName,
                'sectionIsRoot' => (int) $sectionIsRoot, // int so that it can be used with ranking ordering
                'sectionContent' => $section['content']
            ];
        }
    }

    /**
     * Checks if section is a page root, and converts it
     *
     * @param $section
     * @return array two dimensional [$section, $sectionIsRoot]
     */
    private function section($section)
    {
        if ($section === Page::PAGE_ROOT) {
            return ['', true];
        }

        return [$section, false];
    }

    /**
     * Adds anchor/# to url to be able to jump to section
     *
     * @param string $pageUrl
     * @param string $sectionName
     * @param bool $sectionIsRoot
     * @return string
     */
    private function calculateSectionUrl(string $pageUrl, string $sectionName, bool $sectionIsRoot)
    {
        if ($sectionIsRoot) {
            return $pageUrl;
        }

        return $pageUrl . '#' . $this->slugify->slugify($sectionName);
    }

    /**
     * Full title allows search to perform proximity matching by combining breadcrumbs, page title and section title
     *
     * @param array $page
     * @param int $breadcrumbLevel
     * @param string $sectionName
     * @return string
     */
    private function calculateFullTitle(array $page, int $breadcrumbLevel, string $sectionName): string
    {
        $fullTitle = [$page['title'], $sectionName];
        if ($breadcrumbLevel > 1) {
            array_unshift($fullTitle, $page['breadcrumb'][0]);
        }

        return trim(implode(' ', $fullTitle));
    }
}

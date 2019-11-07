<?php namespace ThomasEdwards\BulmaSearch\Pages;

class Cleaner
{
    private $contentRaw = [];

    public function __construct(array $contentRaw)
    {
        $this->contentRaw = $contentRaw;
    }

    public function clean()
    {
        $this
            ->cleanUrl()
            ->cleanBreadcrumbs()
            ->cleanContent();

        return $this->contentRaw;
    }

    private function cleanUrl()
    {
        list($filePath, $url) = explode('bulma/docs/', $this->contentRaw['file']);
        $this->contentRaw['url'] = str_replace('.html', '', $url);
        return $this;
    }

    private function cleanBreadcrumbs()
    {
        $breadcrumb = $this->contentRaw['breadcrumb'];
        unset($breadcrumb[0]);
        unset($breadcrumb[1]);

        // Reset array keys to 0..N
        $breadcrumb = array_values($breadcrumb);

        // Cleans "columns, columns-sizes" to "columns, sizes"
        $breadcrumb = array_map(function ($crumb) use (&$breadcrumb) {
            $firstCrumb = $breadcrumb[0];
            if (substr($crumb, 0, strlen($firstCrumb) + 1) === $firstCrumb . '-') {
                $crumb = substr($crumb, strlen($firstCrumb) + 1);
            }

            return $crumb;
        }, $breadcrumb);

        $this->contentRaw['breadcrumb'] = $breadcrumb;

        return $this;
    }

    private function cleanContent()
    {
        $this->contentRaw = (new Content($this->contentRaw))->clean();
        return $this;
    }
}

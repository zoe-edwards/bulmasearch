<?php

namespace ThomasEdwards\BulmaSearch\Pages;

use FrontMatter;

/**
 * Class Page.
 *
 * @property string      $file
 * @property array       $contentRaw
 * @property array       $sections
 * @property FrontMatter $page
 */
class Page
{
    const PAGE_ROOT = 'PAGE_ROOT';

    private $file;
    private $contentRaw = [];
    private $sections = [];
    private $page;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function sections(): array
    {
        $this->open();

        if ($this->isValid()) {
            $this
                ->load()
                ->clean()
                ->extract()
                ->combine();
        }

        return $this->sections;
    }

    private function open(): self
    {
        $this->page = new FrontMatter($this->file);

        return $this;
    }

    private function load(): self
    {
        $this->contentRaw['file'] = $this->file;
        $this->contentRaw['title'] = $this->page->fetch('title');
        $this->contentRaw['breadcrumb'] = $this->page->fetch('breadcrumb');
        $this->contentRaw['content'] = $this->page->fetch('content');

        return $this;
    }

    private function clean(): self
    {
        $this->contentRaw = (new Cleaner($this->contentRaw))->clean();

        return $this;
    }

    private function extract(): self
    {
        $this->contentRaw = (new Extractor($this->contentRaw))->extract();

        return $this;
    }

    private function combine(): self
    {
        $this->sections = (new Combiner($this->contentRaw))->combine();

        return $this;
    }

    private function isValid()
    {
        return $this->page->keyExists('title') && $this->page->keyExists('breadcrumb');
    }
}

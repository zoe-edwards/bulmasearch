<?php

namespace ThomasEdwards\BulmaSearch\Objects;

class Objects
{
    private $pages;

    public function __construct(array $pages)
    {
        $this->pages = $pages;
    }

    public function build(): array
    {
        return (new Converter($this->pages))->convert();
    }
}

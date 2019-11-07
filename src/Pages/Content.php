<?php namespace ThomasEdwards\BulmaSearch\Pages;

class Content
{
    private $content;

    public function __construct(array $content)
    {
        $this->content = $content;
        return $this;
    }

    public function clean()
    {
        $this->convertAnchorsToHeaders();

        // Most block tags contains examples and code snippets. These are removed because they don't add much
        // value for search. Examples often use tags that aren't the primary topic to the current page.
        $this
            ->removeLiquidBlockTags()
            ->removeTags('{%', '%}')
            ->removeTags('{{', '}}');

        return $this->content;
    }

    /**
     * The documentation uses an include to separate sections within a page.
     * This extracts them and replaces it with a regular <h2> header.
     *
     * e.g. {% include elements/anchor.html name="Offset" %} becomes <h2>Offset</h2>
     */
    private function convertAnchorsToHeaders(): Content
    {
        $includeTag = 'include elements/anchor.html';
        $attributeStartString = 'name="';
        $tagStartString = '{%';
        $tagEndString = '%}';

        while (true) {
            $start = strpos($this->content['content'], $includeTag);

            // No tag found, therefore all tags should be replaced
            if ($start === false) {
                break;
            }

            // Find name attribute start and end
            $attributeStart = strpos($this->content['content'], $attributeStartString, $start);
            $attributeStart += strlen($attributeStartString);
            $attributeEnd = strpos($this->content['content'], '"', $attributeStart);

            // Extract name
            $name = substr($this->content['content'], $attributeStart, $attributeEnd - $attributeStart);

            // Replace include for a header tag
            $tagStart = $start - ($start - strrpos(substr($this->content['content'], 0, $start), $tagStartString));
            $tagEnd = strpos($this->content['content'], $tagEndString, $attributeEnd);
            $headerTag = '<h2>' . $name . '</h2>';
            $this->content['content'] = substr($this->content['content'], 0, $tagStart) . $headerTag . substr($this->content['content'], $tagEnd + strlen($tagEndString));
        }

        return $this;
    }

    /**
     * Remove liquid block tags
     *
     * https://shopify.github.io/liquid/tags/comment/
     * e.g. {% comment %} this is a comment {% endcomment %}
     *
     * Warning: Might cause issues with nested tags with the same tag name
     */
    private function removeLiquidBlockTags(): Content
    {
        $blockEndString = '{% end';
        $blockEndEndString = '%}';
        $blockStartString = '{% ';

        while (true) {
            // Find the last liquid end tag, e.g. {% endhighlight %}
            $blockEnd = strrpos($this->content['content'], $blockEndString);

            // If there's no liquid end tag then there should be no more block tags
            if ($blockEnd === false) {
                break;
            }

            $blockEnd += strlen($blockEndString);

            // Find tag name, e.g. for {% endhighlight %} the tag name is highlight
            $tag = substr($this->content['content'], $blockEnd, strpos($this->content['content'], ' ', $blockEnd) - $blockEnd);

            // Find the start tag, e.g. this would be the first {% highlight proceeding {% endhighlight
            $blockStart = strrpos($this->content['content'], $blockStartString . $tag . ' ');
            if ($blockStart === false) {
                throw new Error('Block start not found');
            }

            $blockEndEnd = strpos($this->content['content'], $blockEndEndString, $blockEnd) + strlen($blockEndEndString);
            $this->content['content'] = substr($this->content['content'], 0, $blockStart) . substr($this->content['content'], $blockEndEnd);
        }

        return $this;
    }

    /**
     * Remove all tags that start with $tagStart and ends with $tagEnd
     *
     * @param string $tagStart
     * @param string $tagEnd
     * @return Content
     */
    private function removeTags(string $tagStart, string $tagEnd): Content
    {
        while (true) {
            $start = strpos($this->content['content'], $tagStart);

            // No tag found, therefore all tags should be remove
            if ($start === false) {
                break;
            }

            $end = strpos($this->content['content'], $tagEnd, $start + strlen($start));
            $this->content['content'] = substr($this->content['content'], 0, $start) . substr($this->content['content'], $end + strlen($tagStart));
        }

        return $this;
    }
}

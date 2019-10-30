<?php

use PHPHtmlParser\Dom;

function cleanContent($content) {
    $content = convertAnchorsToHeaders($content);

    // One of the reason for removing block tags is to remove examples and
    // code snippets. This is because they don't add much value for search since
    // examples often use tags from other documenation sections and pages.
    $content = removeLiquidBlockTags($content);
    $content = removeTags($content, '{%', '%}');
    $content = removeTags($content, '{{', '}}');
    return $content;
}

/**
 * Most of the useful content is found within headers and content
 */
function extractContent($content) {
    $dom = new Dom;
    $dom->setOptions([
        // 'whitespaceTextNode' => false,
    ]);
    
    if(empty($content)) {
        $content = '';
    }
    
    $dom->load($content);
    // Load page content and extract headers and content
    $contents = $dom->find('h1, h2, h3, h4, h5, h6, p, td');

    // Extract page sections from headers
    $headers = [];
    foreach($contents as $content) {
        if(in_array($content->tag->name(), ['h1', 'h2'])) {
            $headers[$content->id()] = $content->text();
        }
    }

    $headers = array_reverse($headers, true);

    $extractedContent = [];
    foreach($contents as $content) {
        $id = $content->id();

        // Place the content under a specific header (or '' for root)
        $section = '';
        foreach($headers as $headerId => $header) {
            if($id >= $headerId) {
                $section = $header;
                break;
            }
        }

        $pageContent = $content->outerHtml();

        // Add a space before each tag so that there's a space between all tag content once the tags are removed
        $pageContent = str_replace('<', ' <', $pageContent);
        $pageContent = strip_tags($pageContent);

        // Replace double+ spaces by one space
        $pageContent = preg_replace('/ {2,}/', ' ', $pageContent);

        $extractedContent[] = [
            'id' => $id,
            'section' => $section,
            'content' => $pageContent
        ];
    }

    usort($extractedContent, function($a, $b) {
        if($a['id'] === $b['id']) {
            return 0;
        }

        return $a['id'] > $b['id'] ? 1 : -1;
    });

    return $extractedContent;
}

function combineSections($contents) {
    $sections = [];
    foreach($contents as $content) {
        $sectionName = $content['section'] ? $content['section'] : 'root';
        if(!isset($sections[$sectionName])) {
            $sections[$sectionName] = [
                'section' => $content['section'],
                'content' => ''
            ];
        }

        $sections[$sectionName]['content'] = trim($sections[$sectionName]['content'] . ' ' . $content['content']);
    }

    // Remove header from section content
    foreach($sections as $sectionKey => $section) {
        if(!empty($section['section'])) {
            if(substr($section['content'], 0, strlen($section['section'])) === $section['section']) {
                $sections[$sectionKey]['content'] = trim(substr($section['content'], strlen($section['section'])));
            }
        }
    }

    return $sections;
}

/**
 * The documenation uses an include to seperate sections within a page
 * e.g. {% include elements/anchor.html name="Offset" %}
 * 
 * Extract the name from the include and replace the include by a header
 * e.g. <h2>Offset</h2>
 */
function convertAnchorsToHeaders($content) {
    $includeTag = 'include elements/anchor.html';
    $attributeStartString = 'name="';
    $tagStartString = '{%';
    $tagEndString = '%}';

    while(true) {
        $start = strpos($content, $includeTag);

        // No tag found, therefore all tags should be replaced
        if($start === false) {
            break;
        }

        // Find name attribute start and end
        $attributeStart = strpos($content, $attributeStartString, $start);
        $attributeStart += strlen($attributeStartString);
        $attributeEnd = strpos($content, '"', $attributeStart);

        // Extract name
        $name = substr($content, $attributeStart, $attributeEnd - $attributeStart);

        // Replace include for a header tag
        $tagStart = $start - ($start - strrpos(substr($content, 0, $start), $tagStartString));
        $tagEnd = strpos($content, $tagEndString, $attributeEnd);
        $headerTag = '<h2>' . $name . '</h2>';
        $content = substr($content, 0, $tagStart) . $headerTag . substr($content, $tagEnd + strlen($tagEndString));
    }
    
    return $content;
}

/**
 * Remove liquid block tags
 * 
 * Example: https://shopify.github.io/liquid/tags/comment/
 * Anything you put between {% comment %} and {% endcomment %}
 * 
 * Warning: Might cause issues with nested tags with the same tag name
 */
function removeLiquidBlockTags($content) {
    $blockEndString = '{% end';
    $blockEndEndString = '%}';
    $blockStartString = '{% ';

    while(true) {
        // Find the last liquid end tag, e.g. {% endhighlight %}
        $blockEnd = strrpos($content, $blockEndString);

        // If there's no liquid end tag then there should be no more block tags
        if($blockEnd === false) {
            break;
        }

        $blockEnd += strlen($blockEndString);

        // Find tag name, e.g. for {% endhighlight %} the tag name is highlight
        $tag = substr($content, $blockEnd, strpos($content, ' ', $blockEnd) - $blockEnd);

        // Find the start tag, e.g. this would be the first {% highlight proceeding {% endhighlight
        $blockStart = strrpos($content, $blockStartString . $tag . ' ');
        if($blockStart === false) {
            throw new Error('Block start not found');
        }

        $blockEndEnd = strpos($content, $blockEndEndString, $blockEnd) + strlen($blockEndEndString);
        $content = substr($content, 0, $blockStart) . substr($content, $blockEndEnd);
    }
    
    return $content;
}

/**
 * Remove all tags that start with $tagStart and ends with $tagEnd
 */
function removeTags($content, $tagStart, $tagEnd) {
    while(true) {
        $start = strpos($content, $tagStart);

        // No tag found, therefore all tags should be remove
        if($start === false) {
            break;
        }

        $end = strpos($content, $tagEnd, $start + strlen($start));
        $content = substr($content, 0, $start) . substr($content, $end + strlen($tagStart));
    }
    
    return $content;
}

function cleanBreadCrumb($breadcrumb) {
    unset($breadcrumb[0]);
    unset($breadcrumb[1]);

    $breadcrumb = array_values($breadcrumb);
    $breadcrumb = array_map(function($crumb) use ($breadcrumb) {
        $firstCrumb = $breadcrumb[0];
        if(substr($crumb, 0, strlen($firstCrumb) + 1) === $firstCrumb . '-') {
            $crumb = substr($crumb, strlen($firstCrumb) + 1);
        }

        return $crumb;
    }, $breadcrumb);

    return $breadcrumb;
}
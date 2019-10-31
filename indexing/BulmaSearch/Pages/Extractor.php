<?php namespace BulmaSearch\Pages;

use PHPHtmlParser\Dom;

class Extractor {

	private $elements = [];
	private $headers = [];
	private $contentRaw = [];
	private $dom;

	public function __construct(array $contentRaw) {
		$this->contentRaw = $contentRaw;
		$this->dom = new Dom();
	}

	/**
	 * Extracts the most useful content of a page. This is usually the headers and some of content (p, td, li...)
	 */
	public function extract() {
		if(empty($this->contentRaw['content'])) {
			return $this->contentRaw;
		}

		$this->elements = $this->elements();
		$this->headers = $this->headers();

		$this
			->extractContent()
			->assignHeaders()
			->sortElements();

		$this->contentRaw['elements'] = $this->elements;
		return $this->contentRaw;
	}

	/**
	 * Load page content and extract headers and content
	 */
	private function elements() {
		$this->dom->load($this->contentRaw['content']);

		// clean up memory for vars no longer used
		unset($this->contentRaw['content']);

		return $this->dom->find('h1, h2, h3, h4, h5, h6, p, td, li');
	}

	/**
	 * Get headers from the page (h1 and h2 only)
	 */
	private function headers() {
		$headers = [];
		foreach($this->elements as $element) {
			if(in_array($element->tag->name(), ['h1', 'h2'])) {
				$headers[$element->id()] = $element->text();
			}
		}

		return array_reverse($headers, true);
	}

	private function extractContent(): Extractor {
		$newElements = [];
		foreach($this->elements as $element) {
			$pageContent = $element->outerHtml();

			// Add a space before each tag so that there's a space between all tag content once the tags are removed
			$pageContent = str_replace('<', ' <', $pageContent);
			$pageContent = strip_tags($pageContent);

			// Replace double+ spaces by one space
			$pageContent = preg_replace('/ {2,}/', ' ', $pageContent);

			$newElements[] = [
				'id' => $element->id(),
				'content' => $pageContent
			];
		}

		$this->elements = $newElements;
		return $this;
	}

	/**
	 * Place the content under a specific header (or '' for root)
	 */
	private function assignHeaders(): Extractor {
		foreach($this->elements as $elementIndex => $element) {
			$this->elements[$elementIndex]['section'] = Page::PAGE_ROOT;
			foreach($this->headers as $headerId => $header) {
				if($element['id'] >= $headerId) {
					$this->elements[$elementIndex]['section'] = $header;
					break;
				}
			}
		}

		return $this;
	}

	/**
	 * When PHPHtmlParser extracts dom, the order isn't preserved. This reorders the elements in their original position.
	 */
	private function sortElements(): Extractor {
		usort($this->elements, function($a, $b) {
			if($a['id'] === $b['id']) {
				return 0;
			}

			return $a['id'] > $b['id'] ? 1 : -1;
		});

		return $this;
	}

}
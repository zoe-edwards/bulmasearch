<?php namespace BulmaSearch\Pages;

class Combiner {

	private $contentRaw;

	public function __construct(array $contentRaw) {
		$this->contentRaw = $contentRaw;
	}

	public function combine() {
		$this
			->combineElementsBySection()
			->removeSectionHeaderPrefix();

		return $this->contentRaw;
	}

	private function combineElementsBySection(): Combiner {
		$this->contentRaw['sections'] = [];
		foreach($this->contentRaw['elements'] as $element) {
			if(!isset($this->contentRaw['sections'][$element['section']])) {
				$this->contentRaw['sections'][$element['section']] = [
					'section' => $element['section'],
					'content' => ''
				];
			}

			$this->contentRaw['sections'][$element['section']]['content'] = trim($this->contentRaw['sections'][$element['section']]['content'] . ' ' . $element['content']);
		}

		// Remove unused vars
		unset($this->contentRaw['elements']);

		// If there's no sections, then it's maybe a section overview page
		// Adding a blank section allows for the page to still be transformed into an object
		if(count($this->contentRaw['sections']) === 0) {
			$this->contentRaw['sections'] = [Page::PAGE_ROOT => ['section' => Page::PAGE_ROOT, 'content' => '']];
		}

		return $this;
	}

	/**
	 * The section content might start with the section name. If it does, remove it.
	 * e.g. Section "Input Colors" with content "Input Colors You can set colors to..." becomes "You can set colors to..."
	 */
	private function removeSectionHeaderPrefix(): Combiner {
		foreach($this->contentRaw['sections'] as $sectionKey => $section) {
			if(!empty($section['section'])) {
				if(substr($section['content'], 0, strlen($section['section'])) === $section['section']) {
					$this->contentRaw['sections'][$sectionKey]['content'] = trim(substr($section['content'], strlen($section['section'])));
				}
			}
		}

		return $this;
	}

}
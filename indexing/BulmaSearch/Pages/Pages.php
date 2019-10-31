<?php namespace BulmaSearch\Pages;

/**
 * Class Pages
 *
 * @package BulmaSearch\Pages
 * @property array $files
 */
class Pages {

	private $files;

	public function __construct(array $files) {
		$this->files = $files;
	}

	public function load(): array {
		return $this->removeInvalid(array_map(function(string $file) {
			return (new Page($file))->sections();
		}, $this->files));
	}

	private function removeInvalid(array $pages): array {
		return array_filter($pages, function($page) {
			return !empty($page);
		});
	}

}
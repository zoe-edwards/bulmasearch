<?php namespace BulmaSearch\Indexers;

use Algolia\AlgoliaSearch\SearchClient;

class Indexer {

	private $index;
	private $algoliaClient;
	private $objects;

	public function build(): Indexer {
		$path = '/bulma/docs/documentation';
		$path = dirname(__FILE__, 4) . $path;
		$this->objects = (new Loader($path))->load();
		return $this;
	}

	public function debug(): Indexer {
		print_r($this->objects);
		return $this;
	}

	public function configure(): Indexer {
		$this->algoliaClient = SearchClient::create(
			getenv('ALGOLIA_APP_ID'),
			getenv('ALGOLIA_API_KEY')
		);

		$this->index = $this->algoliaClient->initIndex('classes_holding');

		$this->index->setSettings([
			'searchableAttributes' => [
				'fullTitle',
				'unordered(sectionTitle)',
				'unordered(pageTitle)',
				'unordered(sectionContent)'
			],
			'customRanking' => [
				'desc(sectionIsRoot)', // prioritizes the top section of each page before other sections of the page
				'asc(pageBreadcrumbLevel)' // prioritizes top level pages before nested pages
			],
			'attributeForDistinct' => 'pageUrl',
			'distinct' => 2,
			'attributesToSnippet' => ['sectionContent'],
			'ignorePlurals' => true
		]);

		$this->index->saveSynonyms([
			[
				'objectID' => 'COLUMNS',
				'type' => 'synonym',
				'synonyms' => ['column', 'columns', 'grid', 'grids']
			]
		]);

		return $this;
	}

	public function upload(): Indexer {
		// Split into two batches because for a reason one batch makes it freeze (maybe throttling... not sure)
		$half = ceil(count($this->objects) / 2);
		$batch1 = array_slice($this->objects, 0, $half);
		$batch2 = array_slice($this->objects, $half);
		$this->index->saveObjects($batch1);
		$this->index->saveObjects($batch2);
		$this->algoliaClient->moveIndex('classes_holding', 'classes');
		return $this;
	}

}
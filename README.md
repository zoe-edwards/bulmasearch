# Bulma Search

[Bulma Search](https://bulmasearch.netlify.com) provides search capability for the [Bulma](https://bulma.io) documentation.

## Usage

To search, go to https://bulmasearch.netlify.com, insert your search query and click on a link to be redirected to the relevant content on the official Bulma documentation.

### Chrome Extension

[<img src="public/img/chrome-install.png">](https://chrome.google.com/webstore/detail/search-for-bulma/melacinmggphfalalkhedbcjgdpnohfl)

You can also install the [Search for Bulma Chrome Extension](https://github.com/patrickdaze/bulma-search-chrome) which uses Bulma Search to add search directly to the Bulma website.

## Development

Want to make tweaks to the Bulma Search code?

### Dependencies
- [Composer](https://getcomposer.org/)
- PHP 7.0+

### Getting Started

1. Clone this repository
2. Copy `.env.sample` to `.env` and replace by your development keys
3. Run `composer install`
4. Run `php src/build.php` to build and upload the index

### Updating Search Index

The GitHub [Search Index Update](.github/workflows/indexer.yml) Action automatically builds and updates the Algolia index when new commits are merged into the `update-index` branch.

---

_The Bulma Search project is not affiliated with nor developed by the core Bulma team._

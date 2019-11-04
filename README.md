# [Bulma Search](https://bulmasearch.netlify.com)

[Bulma Search](https://bulmasearch.netlify.com) provides search capability for the [Bulma](https://bulma.io) documentation.

## Usage

To search, simply go to https://bulmasearch.netlify.com, insert your search query and click on a link to be redirected to the relevant content on the official Bulma documentation.

Alternatively the [Search for Bulma](https://github.com/patrickdaze/bulma-search-chrome) Chrome extension uses Bulma Search to add search directly to the Bulma documentation.

## Development


### Dependencies
- [Composer](https://getcomposer.org/)
- PHP 7.0+

### Getting Started

1. Clone this repository
2. Copy `.env.sample` to `.env` and replace by your development keys
3. Run `composer install`
4. Run `php indexer/build.php` to build and upload the index

---

_The Bulma Search project is not affiliated with nor developed by the core Bulma team._

# [Bulma Search](https://bulmasearch.netlify.com)

[Bulma Search](https://bulmasearch.netlify.com) provides search capability for the [Bulma](https://bulma.io) framework documentation.

_The contents of this Bulma Search repository is not developed by the core Bulma team._

## Usage

To search, simply go to https://bulmasearch.netlify.com, insert your search query and click on a link to be redirected to the relevant content on the official Bulma documentation. 

## Development

**Dependencies:**
- Composer (PHP)
- PHP 7

**Steps:**
1. Clone this repo
2. Copy `.env.sample` to `.env` and replace by your development keys
3. Run `composer install`
4. Run `php indexer/build.php` to build and upload the index
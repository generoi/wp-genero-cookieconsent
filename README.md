# wp-genero-cookieconsent

> A wordpress plugin providing a cookie consent popup using [Cookie Consent by Insites](https://cookieconsent.insites.com).

## Installation

Besides the regular installation step you need to add the following drop-in to your `composer.json` for WP Super Cache to work:

    "dropin-paths": {
      ...
      "web/app/plugins/wp-super-cache/plugins/": [
        "package:generoi/wp-genero-cookieconsent:cookieconsent-supercache.php",
        "type:wordpress-dropin"
      ]
    },

## Requirements

- ACF (@todo remove dependency)

## Features

- Integrates with Google Analytics for Wordpress
- Has layouts for Foundation Reveal and Callout
- Basic fallback for sites without Foundation
- WP Super Cache integration
- Supports Opt in, Opt out and notice
- Supports custom messages and button texts
- Uses WordPress default Privacy Policy if available but can be set to a custom page.

## API

```php
// Specify a Google Analytics code (defaults to `google-analytics-for-wordpress` value)
add_filter('wp-genero-cookieconsent/ga', function ($value) {
  return 'UA-XXXXXX'
});

// Specify a GTM code (defaults to `google-tag-manager` value)
add_filter('wp-genero-cookieconsent/gtm', function ($value) {
  return 'ABC-DEFG'
});

// Modify values sent to JS.
add_filter('wp-genero-cookieconsent/options', function ($options) {
  $options['options']['enabled'] = !WP_DEBUG;
  return $options;
});
```

## Development

Install dependencies

    composer install
    npm install

Run the tests

    npm run test

Build assets

    # Minified assets which are to be committed to git
    npm run build

    # Development assets while developing the plugin
    npm run build:development

    # Watch for changes and re-compile while developing the plugin
    npm run watch

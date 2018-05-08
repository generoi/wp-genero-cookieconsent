# wp-genero-cookieconsent

> A wordpress plugin providing a cookie consent popup using [Cookie Consent by Insites](https://cookieconsent.insites.com).

## Installation

Regular

## Requirements

ACF is required to get an options page but if not available you can provide options using filters.

## Features

- Has layouts for Foundation Reveal and Callout
- Basic fallback for sites without Foundation
- WP Super Cache integration
- Supports Opt in, Opt out and notice
- Supports custom messages and button texts
- Uses WordPress default Privacy Policy if available but can be set to a custom page.

## API

```php
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

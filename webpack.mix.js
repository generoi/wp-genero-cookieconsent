const mix = require('laravel-mix');

mix.sass('assets/styles/main.scss', 'dist/')
  .copy('node_modules/cookieconsent/build/cookieconsent.min.css', 'dist/')
  .js('assets/scripts/main.js', 'dist/')
  .webpackConfig({
    externals: {
      'jquery': 'jQuery',
    },
  })
  .options({
    processCssUrls: false,
  });

const mix = require('laravel-mix');

mix.sass('assets/styles/main.scss', 'dist/')
  .js('assets/scripts/main.js', 'dist/')
  .webpackConfig({
    externals: {
      'jquery': 'jQuery',
    },
  })
  .options({
    processCssUrls: false,
  });

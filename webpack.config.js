var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableSingleRuntimeChunk()
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // uncomment if you use Sass/SCSS files
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: false
     })

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()
    .addStyleEntry('global', './assets/global.scss')
    .addEntry('main', './assets/main.js')
    .addEntry('admin', './assets/js/adminCharts.js')
    .addEntry('allyCharts', './assets/js/allyCharts.js')
    .addEntry('shipsCharts', './assets/js/shipsCharts.js')
    .addEntry('bitcoinCharts', './assets/js/bitcoinCharts.js')
    .addEntry('pdgCharts', './assets/js/pdgCharts.js')
    .addEntry('zombieCharts', './assets/js/zombieCharts.js')
    .enableForkedTypeScriptTypesChecking()
;

module.exports = Encore.getWebpackConfig();

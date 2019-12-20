var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
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
    .enableForkedTypeScriptTypesChecking()
;

module.exports = Encore.getWebpackConfig();

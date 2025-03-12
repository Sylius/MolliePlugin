const path = require('path');
const Encore = require('@symfony/webpack-encore');

const syliusBundles = path.resolve(__dirname, '../../vendor/sylius/sylius/src/Sylius/Bundle/');
const uiBundleScripts = path.resolve(syliusBundles, 'UiBundle/Resources/private/js/');
const uiBundleResources = path.resolve(syliusBundles, 'UiBundle/Resources/private/');
const pluginName = 'mollie';

// Shop config
Encore
    .setOutputPath('public/build/shop/')
    .setPublicPath('/build/shop')
    .addEntry('shop-entry', './assets/shop/entry.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader();

const shopConfig = Encore.getWebpackConfig();
shopConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
shopConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
shopConfig.resolve.alias['sylius/bundle'] = syliusBundles;
shopConfig.resolve.alias['chart.js/dist/Chart.min'] = path.resolve(__dirname, 'node_modules/chart.js/dist/chart.min.js');
shopConfig.name = 'shop';

Encore.reset();

// Admin config
Encore
    .setOutputPath('public/build/admin/')
    .setPublicPath('/build/admin')
    .addEntry('admin-entry', './assets/admin/entry.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader();

const adminConfig = Encore.getWebpackConfig();
adminConfig.resolve.alias['sylius/ui'] = uiBundleScripts;
adminConfig.resolve.alias['sylius/ui-resources'] = uiBundleResources;
adminConfig.resolve.alias['sylius/bundle'] = syliusBundles;
adminConfig.resolve.alias['chart.js/dist/Chart.min'] = path.resolve(__dirname, 'node_modules/chart.js/dist/chart.min.js');
adminConfig.externals = Object.assign({}, adminConfig.externals, { window: 'window', document: 'document' });
adminConfig.name = 'admin';

Encore.reset();

// Mollie Shop config
Encore
    .setOutputPath(`public/build/${pluginName}/shop/`)
    .setPublicPath(`/build/${pluginName}/shop/`)
    .addEntry(`${pluginName}-shop`, path.resolve(__dirname, `../../src/Resources/assets/shop/entry.js`))
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader();

const mollieShopConfig = Encore.getWebpackConfig();
mollieShopConfig.name = `${pluginName}-shop`;

Encore.reset();

// Mollie Admin config
Encore
    .setOutputPath(`public/build/${pluginName}/admin/`)
    .setPublicPath(`/build/${pluginName}/admin/`)
    .addEntry(`${pluginName}-admin`, path.resolve(__dirname, `../../src/Resources/assets/admin/entry.js`))
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader();

const mollieAdminConfig = Encore.getWebpackConfig();
mollieAdminConfig.name = `${pluginName}-admin`;

Encore.reset();

// Mollie Dist config
Encore
    .setOutputPath(`src/Resources/public/`)
    .setPublicPath(`/public/`)
    .addEntry(`${pluginName}-shop`, path.resolve(__dirname, `../../src/Resources/assets/shop/entry.js`))
    .addEntry(`${pluginName}-admin`, path.resolve(__dirname, `../../src/Resources/assets/admin/entry.js`))
    .cleanupOutputBeforeBuild()
    .disableSingleRuntimeChunk()
    .enableSassLoader();

const distConfig = Encore.getWebpackConfig();
distConfig.name = `plugin-dist`;

module.exports = [shopConfig, adminConfig, mollieShopConfig, mollieAdminConfig, distConfig];

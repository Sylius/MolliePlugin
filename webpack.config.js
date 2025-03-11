const path = require('path');
const Encore = require('@symfony/webpack-encore');
const pluginName = 'mollie';

const getConfig = (pluginName, type) => {
	Encore.reset();

	Encore
		.setOutputPath(`public/build/${pluginName}/${type}/`)
		.setPublicPath(`/build/${pluginName}/${type}/`)
		.addEntry(`${pluginName}-${type}`, path.resolve(__dirname, `./src/Resources/assets/${type}/entry.js`))
		.disableSingleRuntimeChunk()
		.cleanupOutputBeforeBuild()
		.enableSourceMaps(!Encore.isProduction())
		.enableVersioning(Encore.isProduction())
		.enableSassLoader();

	const config = Encore.getWebpackConfig();
	config.name = `${pluginName}-${type}`;

	return config;
}

Encore
	.setOutputPath(`src/Resources/public/`)
	.setPublicPath(`/public/`)
	.addEntry(`${pluginName}-shop`, path.resolve(__dirname, `./src/Resources/assets/shop/entry.js`))
	.addEntry(`${pluginName}-admin`, path.resolve(__dirname, `./src/Resources/assets/admin/entry.js`))
	.cleanupOutputBeforeBuild()
	.disableSingleRuntimeChunk()
	.enableSassLoader();

const distConfig = Encore.getWebpackConfig();
distConfig.name = `plugin-dist`;

Encore.reset();

const shopConfig = getConfig(pluginName, 'shop')
const adminConfig = getConfig(pluginName, 'admin')

module.exports = [shopConfig, adminConfig, distConfig];

<p align="center">
    <a href="https://sylius.com" target="_blank">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://media.sylius.com/sylius-logo-800-dark.png">
          <source media="(prefers-color-scheme: light)" srcset="https://media.sylius.com/sylius-logo-800.png">
          <img alt="Sylius Logo." src="https://media.sylius.com/sylius-logo-800.png">
        </picture>
    </a>
</p>

<h1 align="center">Mollie Payments Plugin</h1>

<p align="center"><a href="https://sylius.com/plugins/" target="_blank"><img src="https://sylius.com/assets/badge-official-sylius-plugin.png" width="200"></a></p>

<p align="center">This plugin provides seamless Mollie integration for Sylius stores.</p>

<p align="center">Accept payments through over 20 different methods with Mollie – trusted by over 70,000 businesses in Europe.</p>

---

## Table of Contents

* [Overview](#overview)
* [Demo](#mollie-plugin-demo)
* [Installation](#installation)
  * [Requirements](#requirements)
  * [Usage](#usage)
  * [Customization](#customization)
  * [Testing](#testing)
  * [Recurring subscription (internal CRON)](doc/recurring.md)
  * [Frontend part](#frontend)
* [Recurring payments](doc/recurring.md)
* [Community](#community)
* [Additional Sylius resources for developers](#additional-resources-for-developers)
* [License](#license)
* [Contact](#contact)

---

## Overview

![Screenshot showing payment methods show in shop](doc/payment_methods_shop.png)
![Screenshot showing payment methods show in admin](doc/payment_methods_admin.png)
![Screenshot showing payment method config in admin](doc/payment_method_config.png)

[Mollie](https://www.mollie.com/) is one of the most advanced and developer-friendly payment gateways in Europe. This plugin integrates Mollie into Sylius and is officially certified by Mollie.

> Our mission is to create a greater playing field for everyone. By offering convenient, safe world-wide payment solutions we remove barriers so you could focus on growing your business.

Mollie provides a powerful API allowing webshop and app developers to implement over 20 payment methods with ease. Their services are fast, reliable, and constantly innovating the European payments landscape.

---

## Demo

You can quickly test the plugin using Docker. Just run:

```bash
docker run -p 8080:80 -p 8025:8025 ghcr.io/sylius/mollieplugin:1.0
```

If you'd like to run it in development mode (with debug tools enabled), use:

```bash
docker run -p 8080:80 -p 8025:8025 -e APP_ENV=dev -e APP_DEBUG=1 ghcr.io/sylius/mollieplugin:1.0
```

---

## Installation

#### Beware!

This installation instruction assumes that you're using Symfony Flex. If you don't, take a look at the
[legacy installation instruction](doc/legacy_installation.md). However, we strongly encourage you to use
Symfony Flex, it's much quicker!

#### 1. Ensure that you have `wkhtmltopdf` installed, and that you have the proper path to it set in the .env file (`WKHTMLTOPDF_PATH` and `WKHTMLTOIMAGE_PATH` variables)(Visit [RefundPlugin](https://github.com/Sylius/RefundPlugin) for more information).

#### 2. Require Mollie plugin with composer:

```bash
composer require sylius/mollie-plugin --no-scripts -W
```

#### 3. Update the GatewayConfig entity class with the following code:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use SyliusMolliePlugin\Entity\GatewayConfigInterface;
use SyliusMolliePlugin\Entity\GatewayConfigTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_gateway_config")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_gateway_config')]
class GatewayConfig extends BaseGatewayConfig implements GatewayConfigInterface
{
    use GatewayConfigTrait;

    public function __construct()
    {
        parent::__construct();

        $this->mollieGatewayConfig = new ArrayCollection();
    }
}

```

Ensure that the GatewayConfig resource is overridden in the Sylius configuration file:
```yaml
# config/packages/_sylius.yaml
...

sylius_payum:
    resources:
        gateway_config:
          classes:
              model: App\Entity\Payment\GatewayConfig
```

#### 4. Update the Order entity class with the following code:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use SyliusMolliePlugin\Entity\AbandonedEmailOrderTrait;
use SyliusMolliePlugin\Entity\MolliePaymentIdOrderTrait;
use SyliusMolliePlugin\Entity\OrderInterface;
use SyliusMolliePlugin\Entity\QRCodeOrderTrait;
use SyliusMolliePlugin\Entity\RecurringOrderTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderInterface
{
    use AbandonedEmailOrderTrait;
    use RecurringOrderTrait;
    use QRCodeOrderTrait;
    use MolliePaymentIdOrderTrait;
}

```

Ensure that the Order resource is overridden in the Sylius configuration file:

```yaml
# config/packages/_sylius.yaml
...

sylius_order:
    resources:
        order:
            classes:
                model: App\Entity\Order\Order
```

#### 5. Update the Product entity class with the following code:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductTranslation;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use SyliusMolliePlugin\Entity\ProductInterface;
use SyliusMolliePlugin\Entity\ProductTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct implements ProductInterface
{
    use ProductTrait;

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}

```

Ensure that the Product resource is overridden in the Sylius configuration file:

```yaml
# config/packages/_sylius.yaml
...

sylius_product:
        resources:
            product:
                classes:
                    model: App\Entity\Product\Product
```

#### 6. Update the ProductVariant entity class with the following code:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslation;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use SyliusMolliePlugin\Entity\ProductVariantInterface;
use SyliusMolliePlugin\Entity\RecurringProductVariantTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements ProductVariantInterface
{
    use RecurringProductVariantTrait;

    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }

    public function getName(): ?string
    {
        return parent::getName() ?: $this->getProduct()->getName();
    }
}

```

Ensure that the ProductVariant resource is overridden in the Sylius configuration file:

```yaml
# config/packages/_sylius.yaml
...

sylius_product:
        resources:
          product_variant:
                classes:
                    model: App\Entity\Product\ProductVariant
```

#### 7. Add image directory parameter in `config/packages/_sylius.yaml`:

```yaml
# config/packages/_sylius.yaml

   parameters:
       images_dir: "/media/image/"
```

#### 8. Update your database

After running all the above-mentioned commands, run migrate command
```
bin/console doctrine:migrations:migrate
```

#### 9. Copy Sylius templates overridden in plugin to your templates directory (e.g templates/bundles/):
**Note:** Some directories may already exist in your project

```
mkdir -p templates/bundles/SyliusAdminBundle/
mkdir -p templates/bundles/SyliusShopBundle/
mkdir -p templates/bundles/SyliusUiBundle/
mkdir -p templates/bundles/SyliusRefundPlugin/
```
**Note:** Be aware that the following commands will override your existing templates!

```
cp -R vendor/sylius/mollie-plugin/tests/Application/templates/bundles/SyliusAdminBundle/* templates/bundles/SyliusAdminBundle/
cp -R vendor/sylius/mollie-plugin/tests/Application/templates/bundles/SyliusShopBundle/* templates/bundles/SyliusShopBundle/
cp -R vendor/sylius/mollie-plugin/tests/Application/templates/bundles/SyliusUiBundle/* templates/bundles/SyliusUiBundle/
cp -R vendor/sylius/mollie-plugin/tests/Application/templates/bundles/SyliusRefundPlugin/* templates/bundles/SyliusRefundPlugin/
```

#### 10. Install assets:

```bash
bin/console assets:install
```

**Note:** If you are running it on production, add the `-e prod` flag to this command.

#### 11. Add the payment link cronjob:

```shell script
* * * * * /usr/bin/php /path/to/bin/console mollie:send-payment-link
```

#### 12. Download the [domain validation file](https://www.mollie.com/.well-known/apple-developer-merchantid-domain-association) and place it on your server at:
`public/.well-known/apple-developer-merchantid-domain-association`

## Frontend Asset Management

### 1. Without Webpack

#### Option A: Using Installed Public Assets

1. Install assets:
   ```bash
   bin/console assets:install
   ```

2. Import in Twig templates:

 ```twig
 {{ asset('public/bundles/syliusmollieplugin/mollie/admin.css') }}
 {{ asset('public/bundles/syliusmollieplugin/mollie/admin.js') }}
 {{ asset('public/bundles/syliusmollieplugin/mollie/shop.css') }}
 {{ asset('public/bundles/syliusmollieplugin/mollie/shop.js') }}
 ```

 File locations:
 ```
 public/bundles/syliusmollieplugin/mollie/[file]
 ```

#### Option B: Directly from Vendor
Import directly from vendor files:
```
vendor/sylius/mollie-plugin/src/Resources/public/mollie/admin.css
vendor/sylius/mollie-plugin/src/Resources/public/mollie/admin.js
vendor/sylius/mollie-plugin/src/Resources/public/mollie/shop.css
vendor/sylius/mollie-plugin/src/Resources/public/mollie/shop.js
```

### 2. With Webpack

#### Prerequisites

1. Install required bundle:
   ```bash
   composer require symfony/webpack-encore-bundle
   ```

2. Configuration (`config/packages/webpack_encore.yaml`):
   ```yaml
   webpack_encore:
       output_path: "%kernel.project_dir%/public/build"
       builds:
           mollie-admin: "%kernel.project_dir%/public/build/admin"
           mollie-shop: "%kernel.project_dir%/public/build/shop"
       script_attributes:
           defer: false

   framework:
       assets:
           json_manifest_path: '%kernel.project_dir%/public/build/admin/manifest.json'
   ```

3. Webpack configuration (`webpack.config.js`):
   ```js
   Encore.addEntry(
       'mollie-shop-entry',
       path.resolve(__dirname, 'vendor/sylius/mollie-plugin/src/Resources/assets/shop/entry.js')
   )

   Encore.addEntry(
       'mollie-admin-entry',
       path.resolve(__dirname, 'vendor/sylius/mollie-plugin/src/Resources/assets/admin/entry.js')
   )
   ```

#### Twig Templates Configuration
Ensure the following Twig includes are added to your templates:

1. Shop Templates
**`templates/bundles/SyliusShopBundle/_scripts.html.twig`**:
```twig
<script src="https://js.mollie.com/v1/mollie.js"></script>
{{ encore_entry_script_tags('shop-entry', null, 'mollie-shop') }}
{{ encore_entry_script_tags('plugin-shop-entry', null, 'mollie-shop') }}
```

**`templates/bundles/SyliusShopBundle/_styles.html.twig`**:
```twig
{{ encore_entry_link_tags('shop-entry', null, 'mollie-shop') }}
{{ encore_entry_link_tags('plugin-shop-entry', null, 'mollie-shop') }}
```

1. Admin Templates
**`templates/bundles/SyliusAdminBundle/_scripts.html.twig`**:
```twig
{{ encore_entry_script_tags('admin-entry', null, 'mollie-admin') }}
{{ encore_entry_script_tags('plugin-admin-entry', null, 'mollie-admin') }}
```

**`templates/bundles/SyliusAdminBundle/_styles.html.twig`**:
```twig
{{ encore_entry_link_tags('admin-entry', null, 'mollie-admin') }}
{{ encore_entry_link_tags('plugin-admin-entry', null, 'mollie-admin') }}
```

#### Installation & Build Process

1. If you are using Sylius version <= 1.11 ensure that Node version 12 is currently used:

```bash
nvm install 12
nvm use 12
```

otherwise Node version 14 should be used.

1. Install dependencies:
   ```bash
   yarn add @babel/preset-env bazinga-translator intl-messageformat lodash.get node-sass@4.14.1 shepherd.js@11.0 webpack-notifier
   yarn add --dev @babel/core@7.16.0 @babel/register@7.16.0 @babel/plugin-proposal-object-rest-spread@7.16.5 @symfony/webpack-encore@1.5.0
   ```

2. Build assets:

for development:
   ```bash
   yarn install
   yarn build
   yarn encore dev
   ```

for production:
   ```bash
   yarn install
   yarn build
   yarn encore production
   ```

3. Clear cache:
   ```bash
   php bin/console cache:clear
   ```

## ⚠️ SyliusRefundPlugin Troubleshooting

If you encounter an error related to duplicate transitions in the `sylius_refund_refund_payment` state machine (e.g. multiple `"complete"` transitions from `"new"` state),  
you should **remove the following file** from your project:
```
config/packages/sylius_refund.yaml
```
You should remove it **if your project does not use Symfony Workflow**

## Sylius API
In order to create Mollie payment with Sylius API, the following steps must be followed:

- send the following request to the Sylius API in order to retrieve Mollie payment method configuration: /api/v2/shop/orders/{tokenValue}/payments/{paymentId}/configuration
- tokenValue represents order token which is saved in the sylius_order DB table
- response from this endpoint should be in the following format:

```json
{
  "method": "ideal",
  "issuer": "ideal_ABNANL2A",
  "cardToken": null,
  "amount": {"value":"18.75","currency":"EUR"},
  "customerId": null,
  "description": "000000157",
  "redirectUrl": "{redirect_url}",
  "webhookUrl": "{webhook_url}",
  "metadata": {"order_id":170,"customer_id":22,"molliePaymentMethods":"ideal","cartToken":null,"saveCardInfo":null,"useSavedCards":null,"selected_issuer":"ideal_ABNANL2A","methodType":"Payments API","refund_token":"{token}"},
  "locale": "en_US"
}
```
- create the payment on Mollie, using Mollie API. Response from the above-mentioned step should be put in the request body.
  Request should be sent to the POST: https://api.mollie.com/v2/payments. Bearer token should be sent in the request authorization header.
  Token can be copied from the Mollie admin configuration page.

- after payment has been created, API response will contain checkout field. User should enter this url in the browser.

```json
{
  "checkout": 
    {
    "href": "https://www.mollie.com/checkout/test-mode?method=ideal&token=6.voklib",
    "type": "text/html"
}}
```
- open checkout url in the browser and complete the payment

## Usage

During configuration, first save the keys to the database and then click "Load methods".

### Rendering Mollie credit card form

You can use:

- `SyliusMolliePlugin:DirectDebit:_form.html.twig`
- `@SyliusMolliePlugin/Grid/Action/cancelSubscriptionMollie.html.twig`

See [these examples](tests/Application/templates/bundles/SyliusShopBundle).

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly.
Instead, all security issues must be sent to `security@sylius.com`

## Community

For online communication, we invite you to chat with us & other users on [Sylius Slack](https://sylius-devs.slack.com/).

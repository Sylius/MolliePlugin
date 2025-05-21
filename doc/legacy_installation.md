## Legacy installation (without Symfony Flex)

This installation instruction assumes that you're not using Symfony Flex. If you do, take a look at the
[README](../README.md). We strongly encourage you to use Symfony Flex, it's much quicker!

#### 1. Ensure that you have `wkhtmltopdf` installed, and that you have the proper path to it set in the .env file (`WKHTMLTOPDF_PATH` and `WKHTMLTOIMAGE_PATH` variables)(Visit [RefundPlugin](https://github.com/Sylius/RefundPlugin) for more information).

#### 2. Require Mollie plugin with composer:

```bash
composer require sylius/mollie-plugin:^3.0 --no-scripts -W
```

#### 3. Update the GatewayConfig entity class with the following code:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig as BaseGatewayConfig;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigTrait;

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

        $this->initializeMollieGatewayConfig();
    }
}

```

Ensure that the GatewayConfig resource is overridden in the Sylius configuration file:
```yaml
# config/packages/_sylius.yaml
...

sylius_payment:
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
use Sylius\MolliePlugin\Entity\AbandonedEmailOrderTrait;
use Sylius\MolliePlugin\Entity\MolliePaymentIdOrderTrait;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\QRCodeOrderTrait;
use Sylius\MolliePlugin\Entity\RecurringOrderTrait;

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
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\MolliePlugin\Entity\ProductInterface;
use Sylius\MolliePlugin\Entity\ProductTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct implements ProductInterface
{
    use ProductTrait;
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
use Sylius\MolliePlugin\Entity\ProductVariantInterface;
use Sylius\MolliePlugin\Entity\RecurringProductVariantTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements ProductVariantInterface
{
    use RecurringProductVariantTrait;
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

#### 7. Update the ProductVariant entity class with the following code:

```php
<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;
use Sylius\MolliePlugin\Entity\OnboardingStatusAwareInterface;
use Sylius\MolliePlugin\Entity\OnboardingStatusAwareTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_admin_user")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_admin_user')]
class AdminUser extends BaseAdminUser implements OnboardingStatusAwareInterface
{
    use OnboardingStatusAwareTrait;
}

```

Ensure that the AdminUser resource is overridden in the Sylius configuration file:

```yaml
# config/packages/_sylius.yaml
...

sylius_user:
   resources:
      admin:
         user:
            classes:
               model: App\Entity\User\AdminUser
```

#### 8. Ensure that the plugin dependency is added to your `config/bundles.php` file:

```php
# config/bundles.php

return [
    ...
    winzou\Bundle\StateMachineBundle\winzouStateMachineBundle::class => ['all' => true],
    Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class => ['all' => true],
    Sylius\MolliePlugin\SyliusMolliePlugin::class => ['all' => true],
];
```

#### 9. Import required config in your `config/packages/_sylius.yaml` file:

```yaml
# config/packages/_sylius.yaml

imports:
    ...
    - { resource: "@SyliusMolliePlugin/config/config.yaml" }
```

#### 10. Import the routing in your `config/routes.yaml` file:

```yaml
# config/routes.yaml

sylius_mollie_plugin:
    resource: "@SyliusMolliePlugin/config/routes.yaml"
```

#### 11. Update your database

```
bin/console doctrine:migrations:migrate
```

#### 12. Install frontend assets:

```bash
bin/console assets:install
```

**Note:** If you are running it on production, add the `-e prod` flag to this command.

Add the plugin's assets to your entrypoint files:
```javascript
// assets/admin/entrypoint.js

import '../../vendor/sylius/mollie-plugin/assets/admin/entrypoint';
```

and:

```javascript
// assets/shop/entrypoint.js

import '../../vendor/sylius/mollie-plugin/assets/shop/entrypoint';
```

Install additional dependencies:
```bash
yarn add bazinga-translator intl-messageformat lodash.get shepherd.js@11.0
```

Build assets:

```bash
yarn encode dev # for development
yarn encore production # for production
```

#### 12. Clear cache:

```bash
php bin/console cache:clear
```

## Optional and troubleshooting

1. [Optional] Load fixtures:

   ```bash
   bin/console sylius:fixtures:load
   ```

1. [Optional] Add the payment link cronjob:

   ```shell script
   * * * * * /usr/bin/php /path/to/bin/console mollie:send-payment-link
   ```

1. [Optional] If you want to use ApplePay, you need to add the [domain validation file](https://www.mollie.com/.well-known/apple-developer-merchantid-domain-association) file to your server at:
   ```
   public/.well-known/apple-developer-merchantid-domain-association
   ```

### ⚠️ SyliusRefundPlugin Troubleshooting

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

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly.
Instead, all security issues must be sent to `security@sylius.com`

## Community

For online communication, we invite you to chat with us & other users on [Sylius Slack](https://sylius-devs.slack.com/).

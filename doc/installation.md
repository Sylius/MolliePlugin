# Installation

Ensure that you have `wkhtmltopdf` installed, and that you have the proper path to it set in the .env file (`WKHTMLTOPDF_PATH` and `WKHTMLTOIMAGE_PATH` variables).

#### 1. Require Mollie plugin with composer:

```bash
composer require mollie/sylius-plugin --no-scripts -W
```

#### 2. Follow the installation instructions of RefundPlugin

**Note:** The refund plugin is already required so some steps might not be necessary

https://github.com/Sylius/RefundPlugin/blob/1.5/docs/legacy_installation.md

#### 3. Update the GatewayConfig entity class with the following code:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_gateway_config")
 */
class GatewayConfig extends BaseGatewayConfig implements GatewayConfigInterface
{
    use GatewayConfigTrait;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(
     *     targetEntity="Sylius\MolliePlugin\Entity\MollieGatewayConfig",
     *     mappedBy="gateway",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     */
    protected $mollieGatewayConfig;

    public function __construct()
    {
        parent::__construct();

        $this->mollieGatewayConfig = new ArrayCollection();
    }
}
```
In case if you have installed Sylius version 1.13.x, use the following code instead
```php
<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\MolliePlugin\Entity\GatewayConfigInterface;
use Sylius\MolliePlugin\Entity\GatewayConfigTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig as BaseGatewayConfig;
use Sylius\MolliePlugin\Entity\MollieGatewayConfig;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_gateway_config")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_gateway_config')]
class GatewayConfig extends BaseGatewayConfig implements GatewayConfigInterface
{
    use GatewayConfigTrait;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(
     *     targetEntity="Sylius\MolliePlugin\Entity\MollieGatewayConfig",
     *     mappedBy="gateway",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     */
    #[ORM\OneToMany(
        mappedBy: "gateway",
        targetEntity: MollieGatewayConfig::class,
        cascade: ["all"],
        orphanRemoval: true
    )]
    protected $mollieGatewayConfig;

    public function __construct()
    {
        parent::__construct();

        $this->mollieGatewayConfig = new ArrayCollection();
    }
}

```

You can find more annotation examples under the [tests/Application/src/Entity/*](/tests/Application/src/Entity/) path.

If you don't use annotations, you can also define new Entity mapping inside your `src/Resources/config/doctrine` directory:
```xml
<?xml version="1.0" encoding="UTF-8"?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd"
>
    <mapped-superclass name="App\Entity\Payment\GatewayConfig" table="sylius_gateway_config">
        <one-to-many field="mollieGatewayConfig" target-entity="Sylius\MolliePlugin\Entity\MollieGatewayConfig" mapped-by="gateway" orphan-removal="true">
            <cascade>
                <cascade-all />
            </cascade>
        </one-to-many>
    </mapped-superclass>
</doctrine-mapping>
```
For an example, check [tests/Application/src/Resources/config/doctrine/GatewayConfig.orm.xml](/tests/Application/src/Resources/config/doctrine/GatewayConfig.orm.xml) file.

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

#### 4. Update the Order entity class with the following code: (do this also when updating to plugin version 5.4.0)

```php
<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Sylius\MolliePlugin\Entity\MolliePaymentIdOrderTrait;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\AbandonedEmailOrderTrait;
use Sylius\MolliePlugin\Entity\QRCodeOrderTrait;
use Sylius\MolliePlugin\Entity\RecurringOrderTrait;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\Component\Core\Model\OrderItemInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder implements OrderInterface
{
    use AbandonedEmailOrderTrait;
    use RecurringOrderTrait;
    use QRCodeOrderTrait;
    use MolliePaymentIdOrderTrait;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="abandoned_email")
     */
    protected bool $abandonedEmail = false;

    /**
     * @var ?int
     * @ORM\Column(type="integer", name="recurring_sequence_index", nullable=true)
     */
    protected ?int $recurringSequenceIndex = null;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="qr_code", nullable=true)
     */
    protected ?string $qrCode = null;
    
    /**
     * @var string|null
     * @ORM\Column(type="text", name="mollie_payment_id", nullable=true)
     */
    protected ?string $molliePaymentId = null;

    /**
     * @var MollieSubscriptionInterface|null
     * @ORM\ManyToOne(targetEntity="Sylius\MolliePlugin\Entity\MollieSubscription")
     * @ORM\JoinColumn(name="subscription_id", fieldName="subscription", onDelete="RESTRICT")
     */
    protected ?MollieSubscriptionInterface $subscription = null;

    public function getRecurringItems(): Collection
    {
        return $this
            ->items
            ->filter(function (OrderItemInterface $orderItem) {
                $variant = $orderItem->getVariant();

                return $variant !== null
                    && true === $variant->isRecurring();
            })
            ;
    }

    public function getNonRecurringItems(): Collection
    {
        return $this
            ->items
            ->filter(function (OrderItemInterface $orderItem) {
                $variant = $orderItem->getVariant();

                return $variant !== null
                    && false === $variant->isRecurring();
            })
            ;
    }

    public function hasRecurringContents(): bool
    {
        return 0 < $this->getRecurringItems()->count();
    }

    public function hasNonRecurringContents(): bool
    {
        return 0 < $this->getNonRecurringItems()->count();
    }
}

```
In case if you have installed Sylius version 1.13.x, use the following code instead
```php
<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Sylius\MolliePlugin\Entity\MolliePaymentIdOrderTrait;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\MollieSubscriptionInterface;
use Sylius\MolliePlugin\Entity\AbandonedEmailOrderTrait;
use Sylius\MolliePlugin\Entity\QRCodeOrderTrait;
use Sylius\MolliePlugin\Entity\RecurringOrderTrait;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\Component\Core\Model\OrderItemInterface;
use Doctrine\ORM\Mapping as ORM;

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

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="abandoned_email")
     */
    #[ORM\Column(name: "abandoned_email", type: "boolean")]
    protected bool $abandonedEmail = false;

    /**
     * @var ?int
     * @ORM\Column(type="integer", name="recurring_sequence_index", nullable=true)
     */
    #[ORM\Column(name: "recurring_sequence_index", type: "integer", nullable: true)]
    protected ?int $recurringSequenceIndex = null;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="qr_code", nullable=true)
     */
    #[ORM\Column(name: "qr_code", type: "text", nullable: true)]
    protected ?string $qrCode = null;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="mollie_payment_id", nullable=true)
     */
    #[ORM\Column(name: "mollie_payment_id", type: "text", nullable: true)]
    protected ?string $molliePaymentId = null;

    /**
     * @var MollieSubscriptionInterface|null
     * @ORM\ManyToOne(targetEntity="Sylius\MolliePlugin\Entity\MollieSubscription")
     * @ORM\JoinColumn(name="subscription_id", fieldName="subscription", onDelete="RESTRICT")
     */
    #[ORM\ManyToOne(targetEntity: MollieSubscription::class)]
    #[ORM\JoinColumn(name: "subscription_id", onDelete: "RESTRICT")]
    protected ?MollieSubscriptionInterface $subscription = null;

    public function getRecurringItems(): Collection
    {
        return $this
            ->items
            ->filter(function (OrderItemInterface $orderItem) {
                $variant = $orderItem->getVariant();

                return $variant !== null
                    && true === $variant->isRecurring();
            })
            ;
    }

    public function getNonRecurringItems(): Collection
    {
        return $this
            ->items
            ->filter(function (OrderItemInterface $orderItem) {
                $variant = $orderItem->getVariant();

                return $variant !== null
                    && false === $variant->isRecurring();
            })
            ;
    }

    public function hasRecurringContents(): bool
    {
        return 0 < $this->getRecurringItems()->count();
    }

    public function hasNonRecurringContents(): bool
    {
        return 0 < $this->getNonRecurringItems()->count();
    }
}


```

If you don't use annotations, you can also define new Entity mapping inside your `src/Resources/config/doctrine` directory.
```xml
<?xml version="1.0" encoding="UTF-8"?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd"
>
    <mapped-superclass name="App\Entity\Order\Order" table="sylius_order">
        <field name="abandonedEmail" type="boolean" column="abandoned_email"/>
    </mapped-superclass>
</doctrine-mapping>
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
use Sylius\MolliePlugin\Entity\ProductInterface;
use Sylius\MolliePlugin\Entity\ProductTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\MolliePlugin\Entity\ProductType;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct implements ProductInterface
{
    use ProductTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Sylius\MolliePlugin\Entity\ProductType")
     * @ORM\JoinColumn(name="product_type_id", fieldName="productType", onDelete="SET NULL")
     */
    protected ?ProductType $productType = null;
}
```
In case if you have installed Sylius version 1.13.x, use the following code instead
```php
<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\MolliePlugin\Entity\ProductInterface;
use Sylius\MolliePlugin\Entity\ProductTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\MolliePlugin\Entity\ProductType;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct implements ProductInterface
{
    use ProductTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Sylius\MolliePlugin\Entity\ProductType")
     * @ORM\JoinColumn(name="product_type_id", referencedColumnName="id", onDelete="SET NULL")
     */
    #[ORM\ManyToOne(targetEntity: ProductType::class)]
    #[ORM\JoinColumn(name: "product_type_id", referencedColumnName: "id", onDelete: "SET NULL")]
    protected ?ProductTypeInterface $productType = null;
}

```

If you don't use annotations, you can also define new Entity mapping inside your `src/Resources/config/doctrine` directory.
```xml
<?xml version="1.0" encoding="UTF-8" ?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                  http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd"
                  xmlns:gedmo="http://gediminasm.org/schemas/orm/doctrine-extensions-mapping"
>
    <entity name="App\Entity\Product\Product" table="sylius_product">
        <many-to-one
            field="productType"
            target-entity="Sylius\MolliePlugin\Entity\ProductType"
        >
            <join-column
                name="product_type_id"
                on-delete="SET NULL"
            />
        </many-to-one>
    </entity>
</doctrine-mapping>
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
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use SyliusMolliePlugin\Entity\RecurringProductVariantTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant")
 */
class ProductVariant extends BaseProductVariant
{
    use RecurringProductVariantTrait;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="recurring", nullable="false", options={"default":0})
     */
    private bool $recurring = false;

    /**
     * @var ?int
     * @ORM\Column(type="integer", name="recurring_times", nullable="true")
     */
    private ?int $times = null;

    /**
     * @var ?string
     * @ORM\Column(type="string", name="recurring_interval", nullable="true")
     */
    private ?string $interval = null;

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
In case if you have installed Sylius version 1.13.x, use the following code instead
```php
<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant
{
    use RecurringProductVariantTrait;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="recurring", nullable="false", options={"default":0})
     */
    #[ORM\Column(name: "recurring", type: "boolean", nullable: false, options: ['default' => 0])]
    private bool $recurring = false;

    /**
     * @var ?int
     * @ORM\Column(type="integer", name="recurring_times", nullable="true")
     */
    #[ORM\Column(name: "recurring_times", type: "integer", nullable: true)]
    private ?int $times = null;

    /**
     * @var ?string
     * @ORM\Column(type="string", name="recurring_interval", nullable="true")
     */
    #[ORM\Column(name: "recurring_interval", type: "string", nullable: true)]
    private ?string $interval = null;

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

If you don't use annotations, you can also define new Entity mapping inside your `src/Resources/config/doctrine` directory.

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                  http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd"
                  xmlns:gedmo="http://gediminasm.org/schemas/orm/doctrine-extensions-mapping"
>
    <mapped-superclass name="App\Entity\Product\ProductVariant" table="sylius_product_variant">
        <field name="recurring" type="boolean" column="recurring">
            <options>
                <option name="default">0</option>
            </options>
        </field>
        <field name="times" type="integer" column="recurring_times" nullable="true"/>
        <field name="interval" column="recurring_interval" nullable="true"/>
    </mapped-superclass>
</doctrine-mapping>
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

#### 7. Ensure that the plugin dependency is added to your `config/bundles.php` file:

```php
# config/bundles.php

return [
    ...
    Sylius\MolliePlugin\SyliusMolliePlugin::class => ['all' => true],
];
```

#### 8. Import required config in your `config/packages/_sylius.yaml` file:

```yaml
# config/packages/_sylius.yaml

imports:
    ...
    - { resource: "@SyliusMolliePlugin/config/config.yaml" }
```

#### 9. Add state machine configuration in `config/packages/_sylius.yaml`:
```yaml
# config/packages/_sylius.yaml

winzou_state_machine:
  sylius_order_checkout:
    transitions:
      complete:
        from: [cart, addressed, shipping_selected, shipping_skipped, payment_selected, payment_skipped]
        to: completed
```

#### 10. Add image directory parameter in `config/packages/_sylius.yaml`:
```yaml
# config/packages/_sylius.yaml

   parameters:
       images_dir: "/media/image/"
```

#### 11. Import the routing in your `config/routes.yaml` file:

```yaml
# config/routes.yaml

sylius_mollie_plugin:
    resource: "@SyliusMolliePlugin/Resources/config/routing.yaml"
```
And add `sylius_refund.supported_gateways` parameter to your services

```xml
<parameter key="sylius_refund.supported_gateways" type="collection">
    <parameter>offline</parameter>
    <parameter>mollie</parameter>
</parameter>
```

#### 12. Update your database

In case if you have installed Sylius 1.13.x version, you will need to first remove { resource: "@SyliusRefundPlugin/Resources/config/app/config.yml" }
from acme/config/packages/sylius_refund.yaml

Apply migration to your database: (this is for the plugin fresh installation only)
```
bin/console doctrine:migrations:migrate
```

In case if you are updating from older version of plugin (versions < 5.0), you will need to run the following commands before running migrate command.
```
bin/console doctrine:migrations:version --add --range-from='Sylius\MolliePlugin\Migrations\Version20200513092722' --range-to='Sylius\MolliePlugin\Migrations\Version20220211040328'
bin/console doctrine:migrations:execute --up 'Sylius\MolliePlugin\Migrations\Version20231225151033'
```

After running all the above-mentioned commands, run migrate command
```
bin/console doctrine:migrations:migrate
```

#### 13. Copy Sylius templates overridden in plugin to your templates directory (e.g templates/bundles/):
**Note:** Some directories may already exist in your project

```
mkdir -p templates/bundles/SyliusAdminBundle/
mkdir -p templates/bundles/SyliusShopBundle/
mkdir -p templates/bundles/SyliusUiBundle/
mkdir -p templates/bundles/SyliusRefundPlugin/
```
**Note:** Ba aware that the following commands will override your existing templates!

```
cp -R vendor/mollie/sylius-plugin/tests/Application/templates/bundles/SyliusAdminBundle/* templates/bundles/SyliusAdminBundle/
cp -R vendor/mollie/sylius-plugin/tests/Application/templates/bundles/SyliusShopBundle/* templates/bundles/SyliusShopBundle/
cp -R vendor/mollie/sylius-plugin/tests/Application/templates/bundles/SyliusUiBundle/* templates/bundles/SyliusUiBundle/
cp -R vendor/mollie/sylius-plugin/tests/Application/templates/bundles/SyliusRefundPlugin/* templates/bundles/SyliusRefundPlugin/
```

#### 14. Install assets:

```bash
bin/console assets:install
```

**Note:** If you are running it on production, add the `-e prod` flag to this command.

#### 15. Add the payment link cronjob:

```shell script
* * * * * /usr/bin/php /path/to/bin/console mollie:send-payment-link
```

#### 16. Download the [domain validation file](https://www.mollie.com/.well-known/apple-developer-merchantid-domain-association) and place it on your server at:
`public/.well-known/apple-developer-merchantid-domain-association`

### Frontend

#### 1. Installing assets without `webpack`

If you're not using `webpack`, you can install assets via:

```bash
bin/console assets:install
```

And then import these already built assets into shop/admin `_scripts` and `_styles` .html.twig files:
For example:`templates/bundles/SyliusAdminBundle/_scripts.html.twig ` using:
```
{{ asset('public/bundles/syliusmollieplugin/mollie/admin.css') }}
{{ asset('public/bundles/syliusmollieplugin/mollie/admin.js') }}
{{ asset('public/bundles/syliusmollieplugin/mollie/shop.css') }}
{{ asset('public/bundles/syliusmollieplugin/mollie/shop.js') }}
```
These assets are located in:
```
public/bundles/syliusmollieplugin/mollie/admin.css
public/bundles/syliusmollieplugin/mollie/admin.js
public/bundles/syliusmollieplugin/mollie/shop.css
public/bundles/syliusmollieplugin/mollie/shop.js
```

#### 2. Importing pre-built assets without `webpack`

Another way is to import already built assets directly from mollie source files:
```
vendor/mollie/sylius-plugin/src/Resources/public/mollie/admin.css
vendor/mollie/sylius-plugin/src/Resources/public/mollie/admin.js
vendor/mollie/sylius-plugin/src/Resources/public/mollie/shop.css
vendor/mollie/sylius-plugin/src/Resources/public/mollie/shop.js
```

#### 3. Using `webpack`

Require webpack bundle with composer:

```bash
composer require symfony/webpack-encore-bundle
```

Ensure the following configuration is present in `config/packages/webpack_encore.yaml`:

```
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

Ensure that `mollie-shop-entry` and `mollie-admin-entry` are present in `webpackconfig.js`:

```js
Encore.addEntry(
    'mollie-shop-entry',
    path.resolve(
      __dirname,
      'vendor/mollie/sylius-plugin/src/Resources/assets/shop/entry.js'
    )
)

Encore.addEntry(
    'mollie-admin-entry',
    path.resolve(
        __dirname,
        'vendor/mollie/sylius-plugin/src/Resources/assets/admin/entry.js'
    )
)
```

If you are using Sylius version <= 1.11 ensure that Node version 12 is currently used, otherwise Node version 14 should be used:

```bash
nvm install 12
nvm use 12
```

Ensure you have the following packages installed:

```bash
yarn add @babel/preset-env bazinga-translator intl-messageformat lodash.get node-sass@4.14.1 shepherd.js@11.0 webpack-notifier
yarn add --dev @babel/core@7.16.0 @babel/register@7.16.0 @babel/plugin-proposal-object-rest-spread@7.16.5 @symfony/webpack-encore@1.5.0
```

Run gulp:

```bash
yarn run gulp
```

Build the front-end assets:

```bash
yarn install
yarn build
yarn encore production
```

Update the scheme, since webpack and asset require new tables that are not in the migrations:

```bash
php bin/console doctrine:schema:update --force
```

If you are missing translations, just clear the cache:

```bash
php bin/console cache:clear
```

# Sylius API
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



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
  * [Usage](#usage)
  * [Mollie payment methods synchronization](doc/synchronizing_payment_methods.md)
  * [Recurring subscription (internal CRON)](doc/recurring.md)
* [Recurring payments](doc/recurring.md)
* [Community](#community)

---

## Overview

![Screenshot showing payment methods show in shop](doc/payment_methods_shop.png)
![Screenshot showing payment methods show in admin](doc/payment_methods_admin.png)
![Screenshot showing payment method config in admin](doc/payment_method_config.png)

[Mollie](https://www.mollie.com/) is one of the most advanced and developer-friendly payment gateways in Europe. This plugin integrates Mollie into Sylius and is officially certified by Mollie.

> Our mission is to create a greater playing field for everyone. By offering convenient, safe world-wide payment solutions we remove barriers so you could focus on growing your business.

Mollie provides a powerful API allowing webshop and app developers to implement over 20 payment methods with ease. Their services are fast, reliable, and constantly innovating the European payments landscape.

---

## Installation

#### Beware!

This installation instruction assumes that you're using Symfony Flex and Rector. If you don't, take a look at the
[legacy installation instruction](doc/legacy_installation.md). However, we strongly encourage you to use them, it's much quicker!

#### 1. Require Mollie plugin with composer:

```bash
composer require sylius/mollie-plugin:^3.0 --no-scripts -W
```

#### 2. Add the Mollie rector set:

```diff
   # <project_root>/rector.php

   ...
   + use Sylius\SyliusRector\Set\SyliusMollie;

   return static function (RectorConfig $rectorConfig): void {
       ...
   +   $rectorConfig->sets([SyliusMollie::MOLLIE_PLUGIN_30]);
   };
```
and run it:
```bash
vendor/bin/rector
```

#### 3. Update your database

```
bin/console doctrine:migrations:migrate
```

#### 4. Install frontend assets:

```bash
bin/console assets:install
```

Install additional dependencies:
```bash
yarn add bazinga-translator intl-messageformat lodash.get shepherd.js@11.0
```

Build assets:

```bash
yarn encore dev # for development
yarn encore production # for production
```

#### 5. Clear cache:

```bash
php bin/console cache:clear
```

## Optional and troubleshooting

1. [Optional] To allow refunding orders add the RefundPlugin:

   ```bash
   composer require sylius/refund-plugin:^2.0.2 --no-scripts -W
   ```

   And follow its installation [instructions](https://github.com/Sylius/RefundPlugin/tree/v2.0.1?tab=readme-ov-file#installation).

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

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly.
Instead, all security issues must be sent to `security@sylius.com`

## Community

For online communication, we invite you to chat with us & other users on [Sylius Slack](https://sylius-devs.slack.com/).

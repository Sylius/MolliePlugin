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
* [We are here to help](#we-are-here-to-help)
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

## Installation

### Requirements

We work on stable, supported and up-to-date versions of packages. We recommend you to do the same.

| Package                            | Version                                                    |
|------------------------------------|------------------------------------------------------------|
| PHP                                | ^7.4 \|\| ^8.0                                             |
| ext-json: *                        |                                                            |
| mollie/mollie-api-php              | ^v2.73.0                                                   |
| sylius/admin-order-creation-plugin | ^0.12 \|\| ^0.13 \|\| v0.14 \|\| v0.15.0                   |
| sylius/refund-plugin               | ^1.0                                                       |
| sylius/sylius                      | ~1.9.0 \|\| ~1.10.0 \|\| ~1.11.0 \|\| ~1.12.0 \|\| ~1.13.0 |

👉 For the full installation guide please go to [installation](doc/installation.md)

---

## Usage

During configuration, first save the keys to the database and then click "Load methods".

### Rendering Mollie credit card form

You can use:

- `SyliusMolliePlugin:DirectDebit:_form.html.twig`
- `@SyliusMolliePlugin/Grid/Action/cancelSubscriptionMollie.html.twig`

See [these examples](tests/Application/templates/bundles/SyliusShopBundle).

---

## Customization

You can [decorate](https://symfony.com/doc/current/service_container/service_decoration.html) services and [extend](https://symfony.com/doc/current/form/create_form_type_extension.html) forms.

Run this to see available services:

```bash
bin/console debug:container sylius_mollie_plugin
```

---

## Plugin Development

### Installation

```bash
composer install
cd tests/Application
yarn install
yarn encore dev
bin/console assets:install -e test
bin/console doctrine:database:create -e test
bin/console doctrine:schema:create -e test
bin/console sylius:fixtures:load -e test
symfony server:start
open http://localhost:8080
```
### Webpack Encore Configuration

Make sure you have the following configuration in your `tests/Application/config/packages/webpack_encore.yaml`:

```yaml
webpack_encore:
    output_path: '%kernel.project_dir%/public/build/default'
    builds:
        mollie-admin: '%kernel.project_dir%/public/build/mollie-admin'
        mollie-shop: '%kernel.project_dir%/public/build/mollie-shop'
```

### Twig Templates for Assets

Ensure the following Twig includes are added to your templates so that both base Sylius and Mollie plugin assets are properly loaded.

#### Shop

**`src/Resources/views/Shop/_javascripts.html.twig`**:

```twig
<script src="https://js.mollie.com/v1/mollie.js"></script>
{{ encore_entry_script_tags('shop-entry', null, 'mollie-shop') }}
{{ encore_entry_script_tags('plugin-shop-entry', null, 'mollie-shop') }}
```

**`src/Resources/views/Shop/_stylesheets.html.twig:`**:
```twig
{{ encore_entry_link_tags('shop-entry', null, 'mollie-shop') }}
{{ encore_entry_link_tags('plugin-shop-entry', null, 'mollie-shop') }}
```


**`src/Resources/views/Shop/_stylesheets.html.twig:`**:
```twig
{{ encore_entry_link_tags('shop-entry', null, 'mollie-shop') }}
{{ encore_entry_link_tags('plugin-shop-entry', null, 'mollie-shop') }}
```

#### Admin

**`src/Resources/views/Admin/_javascripts.html.twig`**:

```twig
{{ encore_entry_script_tags('admin-entry', null, 'mollie-admin') }}
{{ encore_entry_script_tags('plugin-admin-entry', null, 'mollie-admin') }}
```
**`src/Resources/views/Admin/_stylesheets.html.twig:`**:

```twig
{{ encore_entry_link_tags('admin-entry', null, 'mollie-admin') }}
{{ encore_entry_link_tags('plugin-admin-entry', null, 'mollie-admin') }}
```

### Frontend

#### Starting server and building assets

* Go to `./tests/Application/` directory
* Run `symfony server:start` in terminal. It will start local server.
* Run `yarn watch` in terminal. It will watch your changes in admin and shop catalogs:
  `../../src/Resources/assets/admin/..`, `../../src/Resources/assets/shop/..`
* Run `yarn dev` in terminal to build your assets once in development mode.
* Run `yarn encore production` in terminal, to build your assets once in production mode - its required before creating every Pull Request.
* All assets (mollie assets + sylius base assets) will be build in:
```
tests/application/public/build/mollie-admin/..
tests/application/public/build/mollie-shop/..
```


#### Rebuilding assets in your root/SRC directory

* `bin/console assets:install`


#### CSS & JS files directory you can edit and work with:

* Admin: go to `./src/Resources/assets/admin/**/`
* Shop: go to `./src/Resources/assets/shop/**/`


## Testing

```
$ bin/behat
$ bin/phpspec run
```
---

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly.
Instead, all security issues must be sent to `security@sylius.com`
___

## Community

For online communication, we invite you to chat with us & other users on [Sylius Slack](https://sylius-devs.slack.com/).


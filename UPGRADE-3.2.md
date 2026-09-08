# UPGRADE FROM 3.2.4 TO 3.2.5

1. Run `yarn install` and `yarn build` to rebuild the shop assets. The bundled
   `assets/shop/js/mollie/app.js` has changed and the shop will not work correctly until it is
   rebuilt.

2. The QR-code and thank-you shop endpoints no longer use the order `tokenValue` introduced in
   3.2.4. Ownership is now proven through the shop session, so both endpoints again accept
   `?orderId=` and validate it against the session:

   - `GET /{_locale}/get-code` serves the current session cart and rejects a foreign `orderId`
     with `HTTP 404`; its JSON response returns `orderId`.
   - `GET /{_locale}/thank-you` expects `?orderId=`, validates it against the session, and
     returns `HTTP 404` when it is missing or does not match.

   If you have overridden `app.js` or link to these endpoints yourself, switch back from
   `orderToken` to `orderId`.

# UPGRADE FROM 3.2.3 TO 3.2.4

1. The shop payment webhook now verifies that the Mollie payment belongs to the referenced
   order before applying its status. Requests whose Mollie payment id does not match the id
   stored for the order are acknowledged with `HTTP 200` and ignored.

1. The thank-you and QR-code shop endpoints no longer accept a raw integer `orderId`; they now
   use the order's non-guessable `orderToken` (`tokenValue`):

   - `GET /{_locale}/thank-you` expects `?orderToken=` instead of `?orderId=` and returns
     `HTTP 404` when the token is missing or unknown.
   - `GET /{_locale}/get-code` expects `?orderToken=`, serves only the current session cart, and
     returns `orderToken` in its JSON response.

   The bundled `assets/shop/js/mollie/app.js` has been updated accordingly. If you have
   overridden that file or link to these endpoints yourself, switch from `orderId` to
   `orderToken`.

1. Run `yarn install` and `yarn build`.

# UPGRADE FROM 3.1 TO 3.2

1. Mollie menu section has been removed

1. The items from the Mollie menu section (except Subscriptions) have been moved to a dropdown within the Payment Method index page.

1. The Subscriptions menu item has been moved to the Sales section.

1. The following twig hook prefixes were updated:
   `sylius_admin.product_type` => `sylius_mollie.admin.product_type`
   `sylius_admin.mollie_subscription` => `sylius_mollie.admin.subscription`
   The previous versions still work via fallback.

1. The Product's `Product type` field has been moved from the general tab to a new `Mollie` tab.
   Subsequently, the following changes were made:

   | Old hook                                                                        | New hook                                                                |
   |---------------------------------------------------------------------------------|-------------------------------------------------------------------------|
   | `sylius_admin.product.create.content.form.sections.general.mollie_product_type` | `sylius_admin.product.create.content.form.sections.mollie.product_type` |
   | `sylius_admin.product.update.content.form.sections.general.mollie_product_type` | `sylius_admin.product.update.content.form.sections.mollie.product_type` |

   The field template path changed from
   `@SyliusMolliePlugin/admin/product/form/sections/general/product_type.html.twig` to `@SyliusMolliePlugin/admin/product/form/sections/mollie/product_type.html.twig`.

1. The Product variant's `Recurring settings` tab has been renamed to `Mollie`.
   Subsequently, the following changes were made:

   | Old hook                                                                            | New hook                                                                  |
   |-------------------------------------------------------------------------------------|---------------------------------------------------------------------------|
   | `sylius_admin.product_variant.create.content.form.side_navigation.mollie_recurring` | `sylius_admin.product_variant.create.content.form.side_navigation.mollie` |
   | `sylius_admin.product_variant.create.content.form.sections.mollie_recurring`        | `sylius_admin.product_variant.create.content.form.sections.mollie`        |
   | `sylius_admin.product_variant.create.content.form.sections.mollie_recurring.body`   | `sylius_admin.product_variant.create.content.form.sections.mollie.body`   |
   | `sylius_admin.product_variant.update.content.form.side_navigation.mollie_recurring` | `sylius_admin.product_variant.update.content.form.side_navigation.mollie` |
   | `sylius_admin.product_variant.update.content.form.sections.mollie_recurring`        | `sylius_admin.product_variant.update.content.form.sections.mollie`        |
   | `sylius_admin.product_variant.update.content.form.sections.mollie_recurring.body`   | `sylius_admin.product_variant.update.content.form.sections.mollie.body`   |

   Field template paths changed:

   | Old template                                                                                              | New template                                                                                    |
   |-----------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------|
   | `@SyliusMolliePlugin/admin/product_variant/form/side_navigation/mollie_recurring.html.twig`               | `@SyliusMolliePlugin/admin/product_variant/form/side_navigation/mollie.html.twig`               |
   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie_recurring.html.twig`                      | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie.html.twig`                      |
   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie_recurring/header.html.twig`               | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie/header.html.twig`               |
   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie_recurring/body.html.twig`                 | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie/body.html.twig`                 |
   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie_recurring/body/recurring.html.twig`       | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie/body/recurring.html.twig`       |
   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie_recurring/body/times.html.twig`           | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie/body/times.html.twig`           |
   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie_recurring/body/interval_amount.html.twig` | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie/body/interval_amount.html.twig` |
   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie_recurring/body/interval_step.html.twig`   | `@SyliusMolliePlugin/admin/product_variant/form/sections/mollie/body/interval_step.html.twig`   |

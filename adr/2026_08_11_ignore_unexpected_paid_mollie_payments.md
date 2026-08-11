# Ignore unexpected paid Mollie payments for superseded checkout sessions

* Status: Accepted
* Date: 2026-08-11

## Context and Problem Statement

When a customer abandons a Mollie checkout mid-payment and later retries — from Sylius, possibly with a different payment method — the plugin creates a new Mollie payment for the order (see issue [#329](https://github.com/Sylius/MolliePlugin/issues/329), PR [#335](https://github.com/Sylius/MolliePlugin/pull/335)). It also best-effort cancels the superseded one, but Mollie only supports programmatic cancellation for a subset of methods (bank transfer and SEPA direct debit) — not for iDEAL, credit card, PayPal, Klarna, BLIK, and others. For those, the old Mollie payment can remain genuinely payable after the new one has already been created.

We asked Mollie support directly what to do about this. Their answer:

> We don't offer canceling an in-flight open payment for most methods to avoid a state mismatch, eg on our end the payment is canceled but upstream (let's say on blik) it still can be paid.
>
> We recommend in these cases to keep a record of all payments created for a given checkout session, and only expect payment from the last one created (eg when the user changed the method). We expect the client keeps track of how many payments were created for a given session, as we can't necessarily link payments to the same session from our end.
>
> Should an unexpected payment become paid, the integration can choose to ignore it (some of our other plugins do that just fine), or create a warning or task for the merchant to act on, or refund the unexpected payment automatically (we do something similar eg in Shopify when we're unable to get final order confirmation from them).

So Mollie explicitly frames "an old, superseded payment becomes paid after a newer one already exists" as expected integration responsibility, not something they will solve for us, and names three acceptable strategies. We need to pick one for this plugin.

## Decision Drivers

* **Correctness/safety** — the order must never end up completed twice, or completed off a payment that isn't the one the merchant actually expects.
* **Implementation scope** — this is a fix for an existing bug report (#329), not a platform for new merchant-facing features; scope creep risks delaying the actual fix.
* **Dependency footprint** — the plugin already treats refund functionality as optional (e.g. `StatusAction::isRefundingPossible()` checks whether `SyliusRefundPlugin` is even installed before attempting a refund).
* **Reversibility** — whichever option we pick now should not block adopting a stronger one later without re-architecting.
* **Precedent** — Mollie states other integrations already ship the "ignore" behavior successfully.

## Considered Options

### Option 1: Ignore the unexpected payment (log only)

* **Good**, because it's the simplest option and matches what Mollie says other integrations already do.
* **Good**, because it keeps Sylius's order state safe by construction: the webhook/notify handlers only ever act on the currently-tracked Mollie payment id, so a stale one becoming paid can never cause a double completion — it's a pure no-op plus a log line.
* **Good**, because it needs no new infrastructure (no admin UI, no dependency on `SyliusRefundPlugin`) and ships entirely within the existing webhook/notify code paths.
* **Good**, because it doesn't foreclose Option 2 or 3 later — the log line, plus the `mollie_payment_ids_history` audit trail already added on `Payment::details`, records everything a future merchant-facing warning or auto-refund would need to act on.
* **Bad**, because the money mismatch (Mollie collected a payment that Sylius's order total doesn't reflect) isn't surfaced anywhere actionable — a merchant who never checks logs or the Mollie dashboard won't notice it happened.
* **Bad**, because reconciliation, if it's ever needed, is manual (grep the log / cross-reference Mollie's dashboard by `metadata.order_id`).

### Option 2: Flag/create a task for the merchant to act on

* **Good**, because it gives the merchant visibility and a concrete next action (refund manually, contact the customer, etc.), with no automatic money movement.
* **Good**, because the merchant stays in control of what happens to the unexpected payment.
* **Bad**, because there is no existing mechanism in this plugin for merchant-facing tasks/notifications — building one (admin dashboard entry, order note, email, or similar) is a feature in its own right, not a bug fix.
* **Bad**, because it still requires manual merchant effort every single time this happens; doesn't scale for a merchant with meaningful abandon-and-retry volume.
* **Bad**, because deciding *where* this should surface (admin grid? order timeline? notification center?) is a product decision that would stall this fix waiting on it.

### Option 3: Automatically refund the unexpected payment

* **Good**, because it's the only option that leaves no lingering money mismatch — the customer gets their money back without merchant involvement.
* **Good**, because Mollie says they do something similar themselves for other integrations (e.g. Shopify, when final order confirmation can't be obtained).
* **Bad**, because it requires refund capability that isn't always available — this plugin's own refund flow is conditional on `SyliusRefundPlugin` being installed (`StatusAction::isRefundingPossible()`), so this option can't be unconditionally relied on.
* **Bad**, because it automates an irreversible money movement based on the same webhook/notify logic that would need to correctly distinguish "genuinely orphaned" from "the payment we actually expected" — a false positive here (e.g. a future regression in that detection logic) would incorrectly refund a legitimate payment with no merchant review step in between.
* **Bad**, because refunds are not instantaneous either, and can themselves fail or be delayed — introducing a second asynchronous process to reason about, on top of the payment-creation race this PR already deals with.

## Decision Outcome

**Chosen option**: **Option 1: Ignore the unexpected payment (log only)**, because it's the option Mollie explicitly validates as sufficient, it requires no new merchant-facing surface or optional-plugin dependency, and it keeps Sylius's own order/payment state safe by construction rather than by process. It is also the least risky to ship as part of a bug-fix PR: it only ever prevents an action (adopting/completing a stale payment) rather than taking a new, irreversible one.

This does **not** eliminate the underlying risk Mollie themselves called out — a real, uncancellable payment can still be collected twice for methods without cancellation support. That risk is inherent to Mollie's own API limitations (confirmed: cancellation only works for bank transfer/SEPA), not something any of the three options fully solves. Options 2 and 3 remain available as future, additive work on top of this one if reconciliation volume ever justifies the investment — nothing in this decision blocks either.

### Where this is implemented

* `src/Payum/Action/NotifyAction.php` (`logOrphanPaidPayment()`) — Payum notify-token webhook path used by the standard checkout flow.
* `src/Controller/Shop/PaymentWebhookController.php` — webhook path used by QR code / Apple Pay / API-storefront flows (mismatch guard).
* `src/Payum/Action/CaptureAction.php` / `src/Api/Controller/SelectMollieMethodAction.php` (`mollie_payment_ids_history` on `Payment::details`) — the audit trail Option 2/3 would build on if ever adopted.

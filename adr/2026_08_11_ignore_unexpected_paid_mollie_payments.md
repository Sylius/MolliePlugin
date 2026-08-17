# Ignore unexpected paid Mollie payments for superseded checkout sessions

* Status: Accepted
* Date: 2026-08-11

## Context and Problem Statement

When a customer abandons a Mollie checkout mid-payment and later retries, possibly with a different payment method, the plugin creates a new Mollie payment for the order (see issue [#329](https://github.com/Sylius/MolliePlugin/issues/329), PR [#335](https://github.com/Sylius/MolliePlugin/pull/335)). It also best-effort cancels the superseded one, but Mollie only supports programmatic cancellation for some payment methods, and which ones is theirs to decide. For the rest, the old Mollie payment can remain genuinely payable after the new one has already been created.

We asked Mollie support directly what to do about this. Their answer suggested choosing one of 3 options.

## What Mollie advised

Their guidance came in two parts.

**On which payment is authoritative** (June): keep a record of every payment created for a checkout session and expect payment only from the last one created, for example after the customer changes method. They cannot reliably link payments to a single session on their end, so that bookkeeping has to live with us. The three options below all concern what to do with an unexpected paid payment **given** that rule, rather than being alternatives to it.

**On the disposition** (2026-08-13): after we described logging the event and leaving the order uncredited, they confirmed that log-only is acceptable, said our reluctance to auto-refund was understandable, and framed the right choice as depending on what the surrounding ecosystem treats as normal integration behaviour.

Two things follow. Log-only is endorsed rather than merely tolerated, so Option 1 is not a compromise we settled for. And the condition attached to it is testable and worth revisiting: it holds as long as other payment integrations in the Sylius ecosystem behave comparably. If one of them starts surfacing this to the merchant, Option 2 comes back into scope.

**What the rule does not cover.** Expecting only the last payment does not prevent a double charge. If two payments from one session are both paid, the money has been collected twice regardless of which one we honour, because open payments cannot be cancelled for most methods. The rule decides only whether the *order* is credited, and the cost is the mirror image: a payment the customer genuinely made can be refused, leaving them charged with no order. This reading is ours and has not been put to Mollie in these terms.

## Decision Drivers

* **Correctness/safety**: the order must never end up completed twice, or completed off a payment that isn't the one the merchant actually expects.
* **Implementation scope**: this is a fix for an existing bug report (#329), not a platform for new merchant-facing features; scope creep risks delaying the actual fix.
* **Dependency footprint**: the plugin already treats refund functionality as optional (e.g. `StatusAction::isRefundingPossible()` checks whether `SyliusRefundPlugin` is even installed before attempting a refund).
* **Reversibility**: whichever option we pick now should not block adopting a stronger one later without re-architecting.
* **Precedent**: Mollie states other integrations already ship the "ignore" behavior successfully.

## Considered Options

### Option 1: Ignore the unexpected payment (log only)

* **Good**, because it's the simplest option and matches what Mollie says other integrations already do.
* **Good**, because it keeps Sylius's order state safe by construction: the webhook/notify handlers only ever act on the currently-tracked Mollie payment id, so a stale one becoming paid can never cause a double completion. It is a pure no-op plus a log line.
* **Good**, because it needs no new infrastructure (no admin UI, no dependency on `SyliusRefundPlugin`) and ships entirely within the existing webhook/notify code paths.
* **Good**, because it doesn't foreclose Option 2 or 3 later. The log line names both the payment that arrived and the one we expected, which is what either of them would act on.
* **Bad**, because the money mismatch (Mollie collected a payment that Sylius's order total doesn't reflect) isn't surfaced anywhere actionable, so a merchant who never checks logs or the Mollie dashboard won't notice it happened.
* **Bad**, because reconciliation, if it's ever needed, is manual (grep the log / cross-reference Mollie's dashboard by `metadata.order_id`).

### Option 2: Flag/create a task for the merchant to act on

* **Good**, because it gives the merchant visibility and a concrete next action (refund manually, contact the customer, etc.), with no automatic money movement.
* **Good**, because the merchant stays in control of what happens to the unexpected payment.
* **Bad**, because there is no existing mechanism in this plugin for merchant-facing tasks/notifications, and building one (admin dashboard entry, order note, email, or similar) is a feature in its own right, not a bug fix.
* **Bad**, because it still requires manual merchant effort every single time this happens; doesn't scale for a merchant with meaningful abandon-and-retry volume.
* **Bad**, because deciding *where* this should surface (admin grid? order timeline? notification center?) is a product decision that would stall this fix waiting on it.

### Option 3: Automatically refund the unexpected payment

* **Good**, because it's the only option that leaves no lingering money mismatch, since the customer gets their money back without merchant involvement.
* **Good**, because Mollie says they do something similar themselves for other integrations (e.g. Shopify, when final order confirmation can't be obtained).
* **Bad**, because it requires refund capability that isn't always available: this plugin's own refund flow is conditional on `SyliusRefundPlugin` being installed (`StatusAction::isRefundingPossible()`), so this option can't be unconditionally relied on.
* **Bad**, because it automates an irreversible money movement based on the same webhook/notify logic that would need to correctly distinguish "genuinely orphaned" from "the payment we actually expected". A false positive there (e.g. a future regression in that detection logic) would incorrectly refund a legitimate payment with no merchant review step in between.
* **Bad**, because refunds are not instantaneous either, and can themselves fail or be delayed, introducing a second asynchronous process to reason about, on top of the payment-creation race this PR already deals with.

## Decision Outcome

**Chosen option**: **Option 1: Ignore the unexpected payment (log only)**, because it's the option Mollie explicitly validates as sufficient, it requires no new merchant-facing surface or optional-plugin dependency, and it keeps Sylius's own order/payment state safe by construction rather than by process. It is also the least risky to ship as part of a bug-fix PR: it only ever prevents an action (adopting/completing a stale payment) rather than taking a new, irreversible one.

This does **not** eliminate the underlying risk Mollie themselves called out. A real, uncancellable payment can still be collected twice for the methods where cancellation is unavailable, and no option here fully solves that. Options 2 and 3 remain available as future, additive work on top of this one if reconciliation volume ever justifies the investment, and nothing in this decision blocks either.

### Where this is implemented

* `src/Payum/Action/NotifyAction.php` (`logOrphanPaidPayment()`), the Payum notify-token webhook path used by the standard checkout flow. Deliberately an **error-level** entry: `MollieLoggerAction::canSaveLog()` discards notices unless the gateway logs everything, which would leave the event with no trace under the more common settings. It is still lost entirely when logging is disabled outright.
* `src/Controller/Shop/PaymentWebhookController.php`, the webhook path used by QR code, Apple Pay and API-storefront flows. Its mismatch guard **predates this decision**, having shipped with the webhook validation work on `3.3`. It is listed because it enforces the same rule, not because this PR added it.

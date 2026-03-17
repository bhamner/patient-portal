# Billing & Subscriptions (Stripe + Laravel Cashier)

Organizations are the **subscribing entity**. Each organization can have a Stripe subscription; staff can create/send invites. Optionally, invite creation can be limited to organizations with an active subscription.

## Setup

1. **Stripe keys** (in `.env`):
   ```env
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

2. **Cashier** is configured to use `App\Models\Organization` as the billable model (`Cashier::useCustomerModel(Organization::class)`). The `organizations` table has Cashier’s customer columns (`stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at`). The `subscriptions` table uses `organization_id`.

3. **Invites and subscription**  
   - By default, any staff member of an organization can create invites (no subscription check).  
   - To require an active subscription (or trial) before creating invites, set in `.env`:
     ```env
     BILLING_INVITES_REQUIRE_SUBSCRIPTION=true
     ```
     Then only organizations that are `subscribed('default')` or `onTrial('default')` can create invites.

## Using Cashier on Organization

- **Create a subscription** (after creating a Stripe product/price):  
  `$organization->newSubscription('default', $priceId)->trialDays(14)->create($paymentMethod);`
- **Check status**: `$organization->subscribed('default')`, `$organization->onTrial('default')`
- **Billing portal**: `$organization->redirectToBillingPortal($returnUrl);`
- **Checkout session** (Stripe Checkout): use Cashier’s subscription checkout methods as in the [Laravel Billing docs](https://laravel.com/docs/billing).

## Webhooks

Configure your Stripe webhook to point to Cashier’s route (e.g. `/stripe/webhook`) and set `STRIPE_WEBHOOK_SECRET` so subscription created/updated/canceled events are handled and your `subscriptions` table stays in sync.

## Existing pieces that work with this

- **Invite flow**: Staff create invites; when `BILLING_INVITES_REQUIRE_SUBSCRIPTION` is true, only subscribed (or trialing) orgs can create invites.
- **`organizations.subscribed_at`**: Optional; you can set it when a subscription is created (e.g. in a webhook) for display or reporting.
- **Policies**: `OrganizationPolicy::createInvite` already enforces staff membership and, when config is on, subscription/trial.

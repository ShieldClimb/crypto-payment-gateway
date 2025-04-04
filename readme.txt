=== ShieldClimb – Crypto Payment Gateway for WooCommerce ===
Contributors: shieldclimb
Donate link: https://shieldclimb.com/
Tags: woocommerce, crypto, payment, cryptocurrency, payment gateway
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.2
WC requires at least: 5.8
WC tested up to: 9.7.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Crypto Payment Gateway with instant payouts—accept cryptocurrency with no registration, no KYC, and no delays. Your crypto, your control.

== Description ==

Enhance your WooCommerce store by accepting cryptocurrency payments seamlessly. This plugin enables instant crypto payments directly on your website with automatic payouts to your wallet—no intermediaries, no holds, and no verification requirements.

Each transaction generates a unique wallet address and QR code, ensuring smooth and secure payments while maintaining complete privacy. Our system automatically detects payments and forwards them to your wallet, even if the customer sends funds via the wrong token or network (for supported cryptocurrencies).

Why choose ShieldClimb’s Crypto Payment Gateway?

* No Registration & No KYC – Start accepting payments instantly.
* Instant Payouts – Funds are forwarded directly to your wallet with no delays.
* Fully On-Site Payment Process – Customers pay on your website without redirections.
* Automatic Payment Detection – Orders are marked as paid instantly.
* Seamless multi-network and multi-currency support – accept crypto payments across multiple blockchains with ease.
* Privacy & Security – Unique wallet addresses for every order enhance anonymity.

=== Features ===

* Auto-Hide Coins Below Minimum – Coins that do not meet the minimum threshold will be automatically removed from checkout.
* Automatic Payment Detection & Order Processing – Orders are marked as paid automatically.
* Instant Access & Approval – No sign-ups, no verifications.
* No Holding of Funds – Payments are forwarded instantly to your wallet.
* Unique Wallet Address per Order – Enhances payment tracking and privacy.
* QR Code Integration – Customers can easily scan and pay.
* Intelligent Misdirected Payment Handling – If a customer sends funds via an incorrect network/token, the system still forwards them to you for supported cryptocurrencies.
* Low Fees – 2% flat rate + blockchain transaction fees.
* Track Transactions in WP-Admin – View TXIDs and payouts within WooCommerce.
* Borderless Payments – Accept crypto globally without restrictions.

The plugin and offered service through [shieldclimb.com Crypto Payment Gateway API](https://shieldclimb.com/) is subject to the [service terms](https://shieldclimb.com/terms-of-service/) and [Privacy Policy](https://shieldclimb.com/privacy-policy/).

== Coingecko API - Third-Party Service Documentation ==

This plugin integrates the [Coingecko API](https://www.coingecko.com/) to fetch cryptocurrency exchange rates, ensuring that payment options are accurately hidden below the correct minimum threshold.

It is subject to the [service terms](https://www.coingecko.com/en/terms) and [Privacy Policy](https://www.coingecko.com/en/privacy).

== Frankfurter API - Third-Party Service Documentation ==

This plugin integrates the [Frankfurter API](https://frankfurter.dev/) to fetch exchange rates, ensuring that payment options are accurately hidden below the correct minimum threshold across stores with different currencies.

= Terms of Service & Privacy Policy =

* The Frankfurter API does not have official Terms of Service or a standalone Privacy Policy.
* According to their website, the API does not collect personal data, but it runs behind Cloudflare for performance, which may collect basic analytics data.
* More details can be found on their website: https://frankfurter.dev/

= Data Usage & Processing =

* This plugin does not send any personal user data to the [Frankfurter API](https://frankfurter.dev/).
* Only currency codes and requested exchange rates are sent in API requests.
* All data comes from the European Central Bank, and the API provides it as-is.

== Installation ==

* Install and activate the plugin.
* Navigate to WooCommerce > Settings > Payments > ShieldClimb Crypto Payment Gateway.
* Enable crypto payments and enter your wallet address.
* Save settings and you will be ready to accept cryptocurrencies directly on your website.

= Minimum Requirements =

* WordPress 5.8 or greater
* PHP version 7.2 or greater

== Frequently Asked Questions ==

= What happens if a cryptocurrency drops below the minimum threshold? =

The plugin automatically hides any cryptocurrency that falls below the required minimum, ensuring customers only see available payment options.

= Do I need to register or verify my identity? =

No. The plugin works without registration or KYC since we never hold your funds. Payouts go directly to your wallet.

= When will I receive my crypto payouts? =

Immediately after payment confirmation—funds are forwarded to your wallet without delays.

= Can I accept multiple cryptocurrencies at the same time? =

Yes, you can enable multiple crypto payment options, and each transaction will be processed individually with its own unique wallet address.

= How can I fix 'There Are No Payment Methods Available' error? =

Follow the guide to [Fix WooCommerce There Are No Payment Methods Available Error](https://shieldclimb.com/blog/fix-no-payment-methods-available-error/)

= Need help? =

For further assistance, contact our [support team](https://shieldclimb.com/contact-us/)

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png

== Changelog ==

= V1.0.0 =

* Initial release
# potpesa
Integrating Lipa Na Mpesa Online payments into Swahilipot Hub Website(or any other).
# Prerequisites
<li>Create an App on the Safaricom Developer Portal.</li>
<li>Access to the Mpesa Web Portal</li>
<li>Wordpress Installation</li>

## Installation
Download from the latest releases of this repository.

## Configuration
Navigate to https://yoursite.tld/wp-admin/admin.php?page=potpesa and fill in the configuration as required.

## Usage
To render a payments form on any page, use the shortcode [POTPESA].
The form shows two fields ( phone and amount ).

## Payment Processing
Once the customer presses the button, they will get a prompt on their phone asking them to confirm the payment by entering their PIN Number.

## Payment Reconciliation
The plugin creates a custom post type called post_payment on request and updates the same when response is received from Safaricom Mpesa.

## Listing Records
The payments received are listed on a page in the admin dashboard.

## Contributing
You are free to contribute to changes to the code and make a pull request and we will merge.

## Licensing
This code is released under the MIT license. See LICENSE

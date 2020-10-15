=== GF Repeater Fields With Standard Format Phone Fields ===

Contributors: pbiron
Tags: gravityforms, repeater fields
Requires at least: 4.6
Requires PHP: 5.6.0
Tested up to: 5.5.1
Stable tag: 0.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Z6D97FA595WSU

Support phone fields with "standard" format in GF repeater fields

== Description ==

[GravityForms](https://gravityforms.com) fields of type [repeater](https://docs.gravityforms.com/repeater-fields/) can
include fields of type [phone](https://docs.gravityforms.com/field-object/#phone).

Unfortunately, as of GF 2.4.20, such `phone` fields with `phoneFormat = 'standard'` don't
get the necessary JS enqueued so that the input masking works.  See [Limitations](https://docs.gravityforms.com/repeater-fields/#limitations)
in the GF documentation about `repeater` fields..

This plugin attempts to remedy that situation, so that `repeater` fields can have
"standard" format `phone` fields.  It is not guaranteed to work in all cases, but it
seems to work for the `repeater` fields I have created.

It supports any number of `repeater` fields in a form, each of which has any number of
`phone` fields with "standard" format.

== Installation ==

From your WordPress dashboard

1. Go to _Plugins > Add New_ and click on _Upload Plugin_
2. Upload the zip file
3. Activate the plugin

== Changelog ==

= 0.1.0 (2020-10-15) =

* init commit.

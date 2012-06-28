VZ URL Fieldtype
================

A fieldtype for ExpressionEngine 2. Also compatible with Pixel & Tonic Matrix and Low Variables.

VZ URL displays a textbox where the user can enter a URL. When the user leaves the field, it will ping the URL they entered and display an error message if it doesn't find a valid webpage there.

Please note that VZ URL will not prevent the user from saving their weblog entry if if cannot validate the URL--it just warns them. This is intentional, perhaps they are linking to a page they have not yet created, or the site they are linking to is currently down but they know the URL is correct. I may add this functionality as an option later on.

For information about VZ URL please visit [my blog](http://elivz.com/blog/single/vz_url_extension/).

Template Tags
-------------

### `{field_name}`

Used as a single tag, the VZ URL field will simply output the URL that was entered.

### `{field_name} ... {/field_name}`

Used as a tag pair, you have access to the entered URL, as well as the component parts of the URL, as parsed by PHP's [parse_url function](http://php.net/manual/en/function.parse-url.php). The following tags are available inside the VZ URL tag pair:

* `{url}`, `{the_url}` - The complete URL as entered. `{the_url}` is an alias that you can use in case your custom field's shortname is `url`
* `{scheme}`
* `{port}`
* `{user}`
* `{pass}`
* `{path}`
* `{query}`
* `{fragment}`

### `{field_name:link [text="Link Text"]}`

Outputs an HTML anchor tag linking to the entered URL. You can use the `text` parameter to specify the link text, otherwise it will default to the URL itself. The following parameters can also be used to set attributes on the `&lt;a&gt;` tag: `accesskey`, `class`, `id`, `rel`, `tabindex`, `target`, `title`.

### `{field_name:redirect}`

Immediately redirects a visitor's browser to the specified URL. Any other code in a template containing this tag will never be displayed.

Requirements
------------

Your server will need to have the CURL library installed and enabled for the link checking to work.

Installation & Updates
----------------------

Download and unzip the extension. Upload the `vz_url` folder to `/system/expression_engine/third_party`, and enable the VZ URL fieldtype and extension.

That's it! Now you can use the VZ URL field type anywhere you were previously using a plain text field. Switching from a text field to a VZ URL field (or vice-versa) will not affect your data.

### Updating from < v2.2

If you are upgrading from VZ URL version 1.1.4 or lower, you need to make two additional changes:

1. Remove the `/themes/third_party/vz_url` folder. It is no longer necessary.
2. Enable the new VZ URL Extension from the `Add-Ons -> Extensions` menu. Until you do this, URL validation will fail.


Support
-------

Please post any questions you might have on the [Devot:ee forum](http://devot-ee.com/add-ons/support/vz-url-extension/viewforum/863). I maintain this fieldtype in my spare time, but I do try to respond to questions as quickly as possible.

ExpressionEngine 1.x Version
----------------------------

I am no longer maintaining an EE1 version of this fieldtype, however you can download [version 2.1.4](https://github.com/elivz/vz_url/zipball/v2.1.4), the last version that included 1.x support. FieldFrame is required.
VZ URL Fieldtype
================

A fieldtype for ExpressionEngine 3. Also compatible with Grid and Low Variables. An [EE2-compatible version](https://github.com/elivz/vz_url/tree/ee2) is available as well.

VZ URL displays a textbox where the user can enter a URL. When the user leaves the field, it will ping the URL they entered and display an error message if it doesn't find a valid webpage there.

Please note that VZ URL will not prevent the user from saving their weblog entry if if cannot validate the URL - it just warns them. This is intentional, perhaps they are linking to a page they have not yet created, or the site they are linking to is currently down but they know the URL is correct.

For more information about VZ URL please visit [Devot:ee](http://devot-ee.com/add-ons/vz-url-extension).

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

Outputs an HTML anchor tag linking to the entered URL. You can use the `text` parameter to specify the link text, otherwise it will default to the URL itself. The following parameters can also be used to set attributes on the `a` tag: `accesskey`, `class`, `id`, `rel`, `tabindex`, `target`, `title`.

### `{field_name:redirect}`

Immediately redirects a visitor's browser to the specified URL. Any other code in a template containing this tag will never be displayed.

Requirements
------------

Your server will need to be running at least PHP 5.3 and have the CURL library installed and enabled for the link checking to work.

Installation & Updates
----------------------

Download and unzip the extension. Upload the `/ee3/vz_url` folder to `/system/user/addons`, and enable VZ URL in the control panel.

That's it! Now you can use the VZ URL field type anywhere you were previously using a plain text field. Switching from a text field to a VZ URL field (or vice-versa) will not affect your data.

Support
-------

Please post any questions you might have on the [Devot:ee forum](http://devot-ee.com/add-ons/support/vz-url-extension/viewforum/863). I maintain this fieldtype in my spare time, but I do try to respond to questions as quickly as possible.

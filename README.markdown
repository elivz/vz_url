VZ URL Fieldtype
================

A fieldtype for ExpressionEngine 2. Also compatible with Pixel & Tonic Matrix and Low Variables.

VZ URL displays a textbox where the user can enter a URL. When the user leaves the field, it will ping the URL they entered and display an error message if it doesn't find a valid webpage there.

Please note that VZ URL will not prevent the user from saving their weblog entry if if cannot validate the URL--it just warns them. This is intentional, perhaps they are linking to a page they have not yet created, or the site they are linking to is currently down but they know the URL is correct. I may add this functionality as an option later on.

Requirements
------------

Your server will need to have the CURL library installed and enabled for the link checking to work.

Installation
------------

Download and unzip the extension. Upload the files, following the folder structure in the download, and enable the VZ URL fieldtype.

That's it! Now you can use the VZ URL field type anywhere you were previously using a plain text field. Switching from a text field to a VZ URL field (or vice-versa) will not affect your data.

For information about VZ URL please visit [my blog](http://elivz.com/blog/single/vz_url_extension/)

Support
-------

Please post any questions you might have on the [Devot:ee forum](http://devot-ee.com/add-ons/support/vz-url-extension/viewforum/863). I maintain this fieldtype in my spare time, but I do try to respond to questions as quickly as possible.

ExpressionEngine 1.x Version
----------------------------

I am no longer maintaining an EE1 version of this fieldtype, however you can download [version 2.1.4](https://github.com/elivz/vz_url/zipball/v2.1.4), the last version that included 1.x support. FieldFrame is required.
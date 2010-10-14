VZ Url
======

A fieldtype for the EE2 or EE 1.6 with the [FieldFrame](http://brandon-kelly.com/fieldframe) extension

VZ Url displays a textbox where the user can enter a url. When the user leaves the field, it will ping the url they entered and display an error message if it doesn't find a valid webpage there.

Please note that VZ URL will not prevent the user from saving their weblog entry if if cannot validate the url--it just warns them. This is intentional, perhaps they are linking to a page they have not yet created, or the site they are linking to is currently down but they know the url is correct. I may add this functionality as an option later on.

Prerequisites
-------------

You must have the FieldFrame and jQuery for the Control Panel extensions installed and enabled. Your server will also need to have the CURL library enabled for the link checking to work.

Installation
------------

Download and unzip the extension. Upload the files, following the folder structure in the download. You will need to enable the VZ URL fieldtype in FieldFrame's extension settings. Then you will need to go back to FieldFrame's settings & show the settings for VZ Url to set the error message that will be shown if the url is invalid.

That's it! Now you can use the VZ URL field type anywhere you were previously using a plain text field. Switching from a Text Field to a VZ Url field (or vice-versa) will not affect your data.

For information about VZ Url please visit [my blog](http://elivz.com/blog/single/vz_url_extension/)
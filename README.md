Contact Form 7 Dotmailer Plugin
======================

## Installation

Git clone the repository into your Wordpress application wp-content/plugins directory.

## Configuration

### Settings
Once the plugin is installed and activated, select the "Dotmailer Contact Form 7 Plugin Settings" under the Settings menu.

Enter your Dotmailer api username and password. A list of address book IDs and names will be generated.  You will need the address book ID of the address book you want to push the data into.

### Contact Form 7

Create your contact form 7 form as normal, and ensure that any data you want to push up to Dotmailer has a name prefix of "dm_" and the name itself must match the contact field you have created in Dotmailer.  For example if you have a contact field of "address_1", the form name needs to be dm_address_1.  

There MUST be a field of dm_emailaddress with the users email.

Add a hidden field into the form with the name of "dm_addressbook" with a value of the ID of the addressbook.

Once this is all set and the form is submitted it should now push the data over to your Dotmailer address book.
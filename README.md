# PHABRIPORT
A workflow for Alfred 3.0 that generates a weekly report based on Phabricator differentials.

The workflow uses Conduit API for Phabricator.
In order to set it up, rename config-example.ini to config.ini and fill in all config options:
- Url of your local instance of Phabricator (e.g. http://domain.com:port/)
- Phabricator User ID (e.g. PHID-USER-egkpgr7pqa3ve57wa6lh)
- API Token can be obtained through the Conduit API Tokens (http://domain.com:port/settings/panel/apitokens/)

Import the workflow on Alfred.

Then open NSAAppleScript on Alfred and fill in:
- Recipient Name
- Recipient Address
- Sender Email

You can also set up a default email signature in AppleScript:
`set message signature of newMessage to signature "Signature #1"`

The shortcut to execute the command is: **⇧ + ⌘ + p**.

If you don't have Alfred, you can still run the **phabriport.php** file in the source folder and pipe the result to your clipboard.


About
===
The workflow is developed by [@donsa](http://twitter.com/nunolopes_99/).

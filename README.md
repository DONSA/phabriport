# PHABRIPORT
A workflow for Alfred 3.0 that generates a weekly report based on Phabricator differentials.

The workflow uses Conduit API for Phabricator.
The API requires some configurations.
In order to set up Conduit API, rename config-example.ini to config.ini and fill in all config options.

Import the workflow on Alfred.

Then open NSAAppleScript on Alfred and fill in:
- Recipient Name
- Recipient Address
- Sender Email

The shortcut to execute the command is **⇧ + ⌘ + p**

If you don't have Alfred, you can still run the phabriport.php in the source folder and copy the result to your clipboard.


About
===
The workflow is developed by [@donsa](http://twitter.com/nunolopes_99/).

AXR Website deployment/build system
===================================

Prerequisities
--------------

- Drush. Installation instructions available at http://drupal.org/project/drush
- Phing. Installation instructions available at
  http://www.phing.info/trac/wiki/Users/Installation

Installing Drupal locally
-------------------------

1. Create a properties file `/deploy/local.properties` with the following
contents:

		drupal.db.host = localhost
		drupal.db.database = axr
		drupal.db.username = root
		drupal.db.password = yourpassword

		drupal.account.name = admin
		drupal.account.pass = drupalpassword

2. Cd into /deploy directory: `cd deploy`
3. Run `phing -f local.xml site-install`

`/` represents the GIT repository root.


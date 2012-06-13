The AXR Project
===============
The AXR Project is a revolutionary open source project that will fundamentally
improve our tools to create web sites and apps.

It's much in the style of current web standards, using plain text files linked
together to create the final site, but with a complete separation of content and
presentation, where the content is pure semantic data written in XML, and the
presentation and behavior layer is written in HSS, a new language based on CSS,
but with many powerful features, such as hierarchical notation, object
orientation, modularization for code reuse, expressions (math), vector graphics,
references (bindings between objects), and a very long etc.

Website
=======
This repository is the place where we edit the development version of the AXR
website. The development version gets automatically deployed to dev.axr.vg
when some new changes are made to it.
When a milestone is hit, the release is deployed to http://axr.vg.

File an issue
=============
You are more than welcome to file any issue you find with the website at
https://github.com/axr/website/issues. A voluteer will answer as soon as
possible.

When creating an issue, make sure to
- Use a short but describing title
- Explain the problem
- If needed include screenshots
- Make sure everything is grammatically correct

Repository manager
===================
This repository is managed by Ragnis Armus (ragnis.armus@gmail.com). Please
read through the entire readme before contacting him, since this may answer your
question.

How the website works
=====================
The website runs mainly on Drupal. For the wiki we use MediaWiki, whose accounts
are linked to Drupal's through an authentication bridge. Soon there will be a
Vanilla forum that will be connected to Drupal using the Orchid module.

Find something to do
====================
You can find work on the github issues tracker:
https://github.com/axr/website/issues. If there is an unassigned or an old task,
post a comment asking for assignment. Make sure you follow the coding standards
and our workflow, which can be found below.

The workflow
============
If you are not used to git, go ahead and read more here:
http://help.github.com/.
Relevant to our workflow: http://help.github.com/fork-a-repo/,
http://help.github.com/send-pull-requests/.

1. Go ahead and fork the AXR/Website repo. You will use that repository as your
	working repo.
2. You can do any git changes you like on the `deveplop` branch. (But make sure
	to read the GIT standards at the end of this file before doing so)
3. When you finish, go to your fork and make a pull request. We'll review it as
	soon as possible. After it has been reviewed and accepted, we will merge it
	into our repo.
4. The files from the repo are automagically deployed to http://dev.axr.vg
	everytime a push is made to the main repository. The deployment system
	installs Drupal and MediaWiki, copies the production database to dev.axr.vg
	and then applies the db delta files from /deploy/db/deltas. To understand
	more about the dbdeploy system, see http://goo.gl/Mzj8P.
5. In order to install the website on your local machine, you first need the
	following dependencies installed. `git`, `php`, `pear`, `phing`, `drush`,
	`mysql`, `mysqldump`, `sendmail`. First you need to run the
	`/deploy/db/scripts/initial.sql`. Then you need to create
	`/deploy/local.properties` file based on the
	`/deploy/local.default.properties` that'll contain your settings. Finally
	you need to cd into `/deploy` and run `phing -f local.xml site-install`,
	that will setup Drupal.

Coding standards
================
These rules apply everywhere:
- Always use tabs for indentation
- No trailing whitespace in the end of lines
- Use whitespace to divide your lines into logical blocks
- You should never omit braces
		// Wrong
		if (condition) do_stuff();

		// Wrong
		if (condition)
			do_stuff();

		// Right
		if (condition)
		{
			do_stuff();
		}

- Do not indent array/object values (This applies to JS, too)
		// Wrong
		array(
			'key'		=> 'value',
			'anotherkey' => 'value'
		)

		// Correct
		array(
			'key' => 'value',
			'anotherkey => 'value' // <- No comma in the end of the last element
		)

- No lines should be longer than 80 characters
- Use `===` instead of `==` whenever possible

PHP standards
-------------
http://area51.phpbb.com/docs/30x/coding-guidelines.html

JavaScript standards
--------------------
Use the same style as the existing code uses. A good example is
`/www/sites/default/themes/axr/js/ajaxsite.js`.

CSS standards
-------------
- Use an id on an element to namespace a whole structure. For example, use the
id `home` for the section home and the class `index` to determine the current
subsection. Another example: id `blog` class `categories`.

- To avoid conflicts in CSS, always start your selectors with an id:

		#home.index
		{
			//styles go here
		}

- Only tabs should be used for indentation.
- Avoid using the descendants selector (whitespace). Instead, use the children
	selector (>).
- If you need to add extra divs for styling purposes, use the element
	`<header>`, the class `content`, and  the element `<footer>` (each one of
	these might be split again, if needed), for those elements which needs this
	kind of partition (where the content goes into the `content` part),
	`extra_0`, `extra_1`, `extra_n`... for empty elements for decoration
	purposes (icons, horizontal rules, etc), and `nested_0`, `nested_1`,
	`nested_n`... for wrappers. Also, for titles, use the class `title`.
	For example:

		<div class="element">
			<div class="nested_0">
				<div class="nested_1">
				<header><h3 class="title">Advantages</h3></header>
					<div class="content">
						<!-- etc -->
					</div>
					<footer></footer>
				</div>
			</div>
		</div>

Git Standards
-------------
- NO changes can be made directly on `master` branch. All  development is done
	on the `develop` branch. The master represents the current production code
- The `develop` branch MUST be stable at ALL times. If you need to push some
	unstable changes, create another branch
- If you create a branch to work on an issue, the branch should be named as
	`issue-<issue number>`
- Use "Close #<issue number>" in the commit message to automagically close the
	issue
- Use present tense in commit messages
- Include issue reference if possible
- When you are proposing, use a pull request
- Make sure your commit messages are grammatically correct (Capital letters,
	punctuation, etc.)
- Common sense
- We use only tabs for indentation
- Don't leave any trailing whitespace the end of the lines
- All files should end with an empty line
- When you write comments, make sure they're grammatically correct


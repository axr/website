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
This repository is the place where the development of the AXR website takes
place.

This repository is managed by Ragnis Armus <ragnis.armus@gmail.com>. Please
read through the entire readme before contacting him, since this may answer your
question.

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

Find something to do
====================
You can find stuff that needs to be done on our GitHub
[issues tracker](https://github.com/axr/website/issues). If there is an
unassigned or old task that you think you can help us with, post a comment on
that issue or send an e-mail the repository manager to have that issue assigned
to you.

Make sure you follow the coding standards and our workflow, which can be found
below.

The workflow
============
You can find a detailed workflow description on our
[wiki](http://axr.vg/wiki/Website_workflow)

Pre-commit hook
===============
It is reccommended that you use our pre-commit hook. In order to do so, simply
copy or symlink the executable at /util/pre-commit to /.git/hooks/, for example:

	ln -s ../../bin/pre-commit "$(git rev-parse --show-toplevel)/.git/hooks/pre-commit"

Coding standards
================
These rules apply everywhere:
- Always use tabs for indentation
- **No trailing whitespace in the end of lines**
- Every file ends with a blank line
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
			'key'        => 'value',
			'anotherkey' => 'value'
		)

		// Correct
		array(
			'key' => 'value',
			'anotherkey => 'value' // <- No comma in the end of the last element
		)

- Lines should be longer than 80 characters
- Use `===` instead of `==` whenever possible

PHP standards
-------------
http://area51.phpbb.com/docs/30x/coding-guidelines.html

CSS standards
-------------
- Use an id on an element to namespace a whole structure. For example, use the
id `home` for the section home and the class `index` to determine the current
subsection. Another example: id `blog` class `categories`.

- To avoid conflicts in CSS, always start your selector with an id:

		#blog.categories
		{
			//styles go here
		}

- Use use the children selector (`>`) to make your selectors more specific


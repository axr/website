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
[wiki](http://wiki.axrproject.org/website/workflow).

Pre-commit hook
===============
It is recommended that you use our pre-commit hook. In order to do so, simply
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
We use phpBB coding standards: http://area51.phpbb.com/docs/30x/coding-guidelines.html

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

window['App'] = window['App'] || {};

(new Rsrc.File('js/code_box.js')).provide(function ()
{
	var CodeBox = function (element)
	{
		var that = this;

		/**
		 * The element.
		 */
		this.element = element;

		this.render();
	};

	/**
	 * Replace the code block with something
	 */
	CodeBox.prototype.replace_with = function (replacement)
	{
		this.element.replaceWith(replacement);
	};

	/**
	 * Render a code block
	 */
	CodeBox.prototype.render = function ()
	{
		var that = this;

		(new App.Template('code_box')).request(function (template, error)
		{
			if (error)
			{
				return;
			}

			var code_element = that.element.find('pre > code');
			var lines_raw = code_element.html().split('\n');
			var lines = [];

			for (var i = 0, c = lines_raw.length; i < c; i++)
			{
				if (i + 1 === c && lines_raw[i].length === 0)
				{
					// Don't insert the last blank line
					break;
				}

				lines.push({
					number: i + 1,
					line: lines_raw[i]
				});
			}

			var html = Mustache.render(template, {
				language: code_element.attr('data-language'),
				lines: lines
			});

			that.replace_with(html);
		});
	};

	/**
	 * Find all code blocks in the provided element and render them
	 *
	 * @param {object} element
	 */
	CodeBox.find_all = function (element)
	{
		$(element).find('pre > code').each(function (i, element)
		{
			new CodeBox($(element).parent());
		});
	};

	window['App']['CodeBox'] = CodeBox;
});

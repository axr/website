(function ()
{
	var SearchBar = function (element)
	{
		var that = this;

		this.element = element;

		this.sources = {
			www: {name: 'Website', selected: false},
			hss: {name: 'HSS doc', selected: false},
			wiki: {name: 'Wiki', selected: false}
		};

		this.set_query = function (query)
		{
			this.element.find('input[name=query]').val(query);
			this.clean_query();
		};

		this.clean_query = function (initial)
		{
			var query = this.element.find('input[name=query]').val();
			var old_query = query;
			var match_groups = query.match(/\bsource:(\w+)\b/g) || [];

			if (initial === true && match_groups.length === 0)
			{
				for (var key in this.sources)
				{
					this.sources[key].selected = true;
				}
			}

			// Take sources from the query and mark them selected
			for (var i = 0, c = match_groups.length; i < c; i++)
			{
				var match = match_groups[i].match(/\bsource:(\w+)\b/);

				if (typeof this.sources[match[1]] === 'object')
				{
					this.sources[match[1]].selected = true;
					query = query.replace(new RegExp('\\bsource:' + match[1] + '\\b', 'g'), '');
				}
			}

			query = query.replace(/[ ]+/g, ' ').replace(/(^\s|\s$)/, '')

			if (query !== old_query)
			{
				this.element.find('input[name=query]').val(query);
			}

			this.update_options_ui();
		};

		this.update_options_ui = function ()
		{
			for (var key in this.sources)
			{
				var element = this.element.find('.options .sources a[data-key=' + key + ']');

				if (this.sources[key].selected === true)
				{
					element.attr('data-selected', 1).addClass('selected');
				}
				else
				{
					element.attr('data-selected', 0).removeClass('selected');
				}
			}
		};

		this.build_query = function ()
		{
			this.clean_query();

			var query = this.element.find('input[name=query]').val();
			var all_sources_selected = true;

			for (var key in this.sources)
			{
				if (this.sources[key].selected !== true)
				{
					all_sources_selected = false;
					break;
				}
			}

			if (all_sources_selected === false)
			{
				// Append new sources to the query
				for (var key in this.sources)
				{
					if (this.sources[key].selected === true)
					{
						query += ' source:' + key.replace(/\W/, '');
					}
				}
			}

			return query;
		};

		this.submit = function ()
		{
			var query = that.build_query();
			var query_encoded = encodeURIComponent(query)
				.replace(/%20/g, '+')
				.replace(/%3A/g, ':')
				.replace(/%40/g, '@');

			window.location = '/q/' + query_encoded;
		};

		// Load all the source buttons
		for (var key in this.sources)
		{
			this.element.find('.options .sources').append($('<a>')
				.attr('data-key', key)
				.attr('data-selected', !!this.sources[key].selected + 0)
				.html(this.sources[key].name));
		}

		this.element.on('click dblclick', '.options .sources a', function (e)
		{
			e.preventDefault();

			var key = $(this).attr('data-key');

			if (e.type === 'click')
			{
				var selected_count = 0;

				for (var key2 in that.sources)
				{
					selected_count += !!that.sources[key2].selected + 0;
				}

				if (that.sources[key] !== undefined)
				{
					if (that.sources[key].selected === true && selected_count === 1)
					{
						// Don't allow to deselect the last source
						return;
					}

					that.sources[key].selected = !that.sources[key].selected;
				}
			}
			else if (e.type === 'dblclick')
			{
				var current_selected = that.sources[key].selected;

				for (var key2 in that.sources)
				{
					if (current_selected === true)
					{
						that.sources[key2].selected = (key !== key2);
					}
					else
					{
						that.sources[key2].selected = (key === key2);
					}
				}
			}

			that.update_options_ui();
		});

		this.element.on('focusin focusout keyup', 'input[name=query]', function (e)
		{
			switch (e.type)
			{
				case 'focusin':
					if ($(this).val() === $(this).attr('data-placeholder'))
					{
						$(this).removeClass('inactive').val('');
					}
				break;

				case 'focusout':
					if ($(this).val().replace(/\s+/, '').length === 0)
					{
						$(this)
							.val($(this).attr('data-placeholder'))
							.addClass('inactive');
					}
				break;

				case 'keyup':
					if (e.keyCode === 13)
					{
						that.submit();
					}
					else
					{
						that.clean_query();
					}
				break;
			}
		});

		this.element.on('click', 'button[name=submit]', function (e)
		{
			that.submit();
		});

		this.clean_query(true);
	};

	new SearchBar($('#search_box'));
})();

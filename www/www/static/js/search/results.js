(function ()
{
	var LoadAnimation = function (canvas)
	{
		const T_ON = 80;
		const T_OFF = 25;

		var request_frame = (function ()
		{
			return window.requestAnimationFrame ||
				window.webkitRequestAnimationFrame ||
				window.mozRequestAnimationFrame ||
				function (callback)
				{
					window.setTimeout(callback, 1000 / 60);
				}
		})();

		var ctx = $(canvas).get(0).getContext('2d');
		ctx.canvas.width = $(canvas).width();
		ctx.canvas.height = $(canvas).height();

		var imagedata = ctx.createImageData(ctx.canvas.width, 1);
		var pixels = [];
		var spawn_counter = T_OFF * -1;

		// Initial pixel data
		{
			var phase = 1;
			var phase_i = 0;

			for (var i = 0, c = ctx.canvas.width; i < c; i++)
			{
				pixels[i] = phase;

				if (phase === 0 && phase_i >= T_OFF)
				{
					phase = 1;
					phase_i = 0;
				}
				else if (phase === 1 && phase_i >= T_ON)
				{
					phase = 0;
					phase_i = 0;
				}

				phase_i++;
			}
		}

		var math_frame = function ()
		{
			var end = ctx.canvas.width - 1;

			for (var i = ctx.canvas.width - 1; i >= 0; i--)
			{
				if (i === 0)
				{
					pixels[0] = 0;
				}
				else
				{
					pixels[i] = pixels[i - 1];
				}
			}

			if (spawn_counter >= 0)
			{
				pixels[0] = 1;
			}

			if (spawn_counter >= T_ON)
			{
				spawn_counter = T_OFF * -1;
			}
			else
			{
				spawn_counter++;
			}
		};

		(function animation_loop ()
		{
			request_frame(animation_loop);

			for (var i = 0, c = pixels.length; i < c; i++)
			{
				imagedata.data[i*4] = 0xD0;
				imagedata.data[i*4 + 1] = 0x96;
				imagedata.data[i*4 + 2] = 0x10;
				imagedata.data[i*4 + 3] = pixels[i] === 1 ? 0xFF : 0x00;
			}

			for (var y = 0; y < ctx.canvas.height; y++)
			{
				ctx.putImageData(imagedata, 0, y);
			}

			if ($(canvas).width() != ctx.canvas.width ||
				$(canvas).height() != ctx.canvas.height)
			{
				ctx.canvas.width = $(canvas).width();
				ctx.canvas.height = $(canvas).height();

				pixels = [];
				imagedata = ctx.createImageData(ctx.canvas.width, 1);
			}
		})();

		setTimeout(function math_loop ()
		{
			for (var i = 0; i < 5; i++)
			{
				math_frame();
			}

			setTimeout(math_loop, 10);
		}, 10);
	};

	var SearchBar = function (element)
	{
		var that = this;

		this.element = element;
		this.rp = null;

		this.sources = {
			www: {name: 'Website', selected: false},
			hss: {name: 'HSS doc', selected: false},
			spec: {name: 'Spec', selected: false},
			wiki: {name: 'Wiki', selected: false},
			irc: {name: 'IRC', selected: false}
		};

		this.set_query = function (query)
		{
			this.element.find('input[name=query]').val(query);
			this.clean_query();
		};

		this.clean_query = function (initial)
		{
			var query = this.element.find('input[name=query]').val();
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

			this.element.find('input[name=query]').val(query.replace(/\s+/, ' '));
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
			if (that.rp !== null)
			{
				Core.Router.instance().navigate('/q/' + encodeURIComponent(that.build_query()));
			}
			else
			{
				window.location = '/q/' + encodeURIComponent(that.build_query());
			}
		};

		// Load all the source buttons
		for (var key in this.sources)
		{
			this.element.find('.options .sources').append($('<a>')
				.attr('data-key', key)
				.attr('data-selected', !!this.sources[key].selected + 0)
				.html(this.sources[key].name));
		}

		this.element.on('click', '.options .sources a', function (e)
		{
			var key = $(this).attr('data-key');
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

			that.update_options_ui();
			that.submit();
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
					that.clean_query();
				break;
			}
		});

		this.element.on('click', 'button[name=submit]', function (e)
		{
			that.submit();
		});

		this.clean_query(true);
	};

	var ResultsPage = function ()
	{
		var that = this;

		this.query = null;
		this.next_offset = 0;
		this.ajax = [];

		this.set_query = function (query, params)
		{
			this.query = query;
			this.next_offset = 0;

			// Abort all current ajax requests
			while (this.ajax.length > 0)
			{
				this.ajax.pop().abort();
			}

			this.set_attr('loading', false);
			this.set_attr('has_more', false);
			this.set_attr('no_results', false);

			$('#results ._results').empty();

			var query_simple = query.replace(/\b\w+:\w+\b/g, '').replace(/\s+/, ' ');
			$('#breadcrumb span.current').html('Results for <strong>' + query_simple + '</strong>');
		};

		this.load_more = function ()
		{
			if (this.query === null || this.ajax.length > 0)
			{
				return;
			}

			this.set_attr('loading', true);
			this.set_attr('has_more', false);
			this.set_attr('no_results', false);

			this.ajax.push($.ajax({
				url: '/q.json',
				data: {
					query: this.query,
					offset: this.next_offset
				},
				dataType: 'json',
				success: function (data)
				{
					for (var i = 0, c = data.results.length; i < c; i++)
					{
						$('#results ._results').append(data.results[i]);
					}

					Core.CodeBox.find_all($('#results ._results'));

					that.next_offset = data.next;
					that.ajax.pop();

					that.set_attr('loading', false);
					that.set_attr('no_results', data.results.length === 0);
					that.set_attr('has_more', data.has_more);
				}
			}));
		};

		this.set_attr = function (name, value)
		{
			var selectors = {
				loading: '#results .loading',
				no_results: '#results .no_results',
				has_more: '#results .load_more',
			};

			if (selectors[name] !== undefined)
			{
				$(selectors[name]).css('display', value ? 'block' : 'none');
			}
		};

		new LoadAnimation($('#results .loading canvas'));
	};

	var rp = new ResultsPage();
	var sb = new SearchBar($('#search_box'));
	sb.rp = rp;

	Core.Router.instance().once(/^\/q\//, function ()
	{
		$('#results article header .info').tipsy({
			live: true,
			gravity: 'e'
		});

		$('#results .load_more a').on('click', function (e)
		{
			e.preventDefault();
			rp.load_more();
		});
	});

	Core.Router.instance().on(/^\/q\//, function ()
	{
		var matchdata = Core.Router.instance().url().match(/\/q\/(.+)/) || [];
		var query = decodeURIComponent(matchdata[1].replace('+', ' '));

		rp.set_query(query);
		sb.set_query(query);

		if ($('#results ._results').children().length === 0)
		{
			rp.load_more();
		}
	});
})();

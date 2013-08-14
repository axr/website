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
		this.sources = {
			www: {name: 'Website', selected: false},
			hss: {name: 'HSS doc', selected: false},
			spec: {name: 'Spec', selected: false},
			wiki: {name: 'Wiki', selected: false},
			irc: {name: 'IRC', selected: false}
		};

		this.update_options = function ()
		{
			var query = this.element.find('input[name=query]').val();
			var match_groups = query.match(/\bsource:(\w+)\b/g) || [];

			if (match_groups.length > 0)
			{
				for (var key in this.sources)
				{
					this.sources[key].selected = false;
				}

				for (var i = 0, c = match_groups.length; i < c; i++)
				{
					var match = match_groups[i].match(/\bsource:(\w+)\b/);
					(this.sources[match[1]] || {}).selected = true;
				}
			}
			else
			{
				for (var key in this.sources)
				{
					this.sources[key].selected = true;
				}
			}

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

		this.update_query = function ()
		{
			var query = this.element.find('input[name=query]').val();
			var match_groups = query.match(/\bsource:(\w+)\b/g) || [];

			// Remove sources that are no longer selected from the query
			for (var i = 0, c = match_groups.length; i < c; i++)
			{
				var match = match_groups[i].match(/\bsource:(\w+)\b/);

				if ((this.sources[match[1]] || {}).selected !== true)
				{
					var key_safe = match[1].replace(/\W/, '');
					query = query.replace(new RegExp('\\bsource:' + key_safe + '\\b'), '');
				}
			}

			// Append new sources to the query
			for (var key in this.sources)
			{
				var key_safe = key.replace(/\W/, '');

				if (this.sources[key].selected === true &&
					!(new RegExp('\\bsource:' + key_safe + '\\b')).test(query))
				{
					query += ' source:' + key_safe;
				}
			}

			this.element.find('input[name=query]')
				.removeClass('inactive')
				.val(query.replace(/\s+/, ' '));
		};

		this.submit = function ()
		{
			window.location = '/q/' +
				encodeURIComponent(this.element.find('input.query').val());
		};

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
			if (that.sources[key] !== undefined)
			{
				that.sources[key].selected = !that.sources[key].selected;
			}

			that.update_query();
			that.update_options();
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
					that.update_options();
				break;
			}
		});

		this.element.on('click', 'button[name=submit]', function (e)
		{
			that.submit();
		});

		this.update_options();
	};

	var ResultsPage = function ()
	{
		var that = this;

		this.query = null;
		this.next_offset = 0;
		this.load_anim = new LoadAnimation($('#results .loading canvas'));

		this.is_loading = false;
		this.has_results = false;
		this.has_more = true;

		this.update_ui = function ()
		{
			$('#results .loading').css({
				display: this.is_loading ? 'block' : 'none'
			});

			$('#results .load_more').css({
				display: this.has_more ? 'block' : 'none'
			});

			$('#results .no_results').css({
				display: (!this.is_loading && !this.has_results) ? 'block' : 'none'
			});
		};

		this.load_more = function ()
		{
			if (this.is_loading === true)
			{
				return;
			}

			this.is_loading = true;
			this.has_more = false;
			this.update_ui();

			$.ajax({
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

					if (data.results.length > 0)
					{
						that.has_results = true;
					}

					that.is_loading = false;
					that.has_more = data.has_more;
					that.update_ui();

					that.next_offset = data.next;
				}
			});
		};
	};

	var rp = new ResultsPage();
	var sb = new SearchBar($('#search_box'));

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
		rp.query = $('#results ._results').attr('data-query');

		if ($('#results ._results').children().length === 0)
		{
			rp.load_more();
		}
	});
})();

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

window.App = window.App || {};

(function (App)
{
	/**
	 * Get current object name
	 *
	 * @return string
	 */
	var getObjectName = function ()
	{
		var match = window.location.pathname.match(/^\/([^\/]+)/);
		return (match || [])[1];
	};

	/**
	 * Scroll to property
	 *
	 * @param string property
	 */
	var scrollToProperty = function (property)
	{
		$('#hssdoc_obj section.prop_item').each(function (i, element)
		{
			if ($(element).attr('data-property') === property)
			{
				$('html, body').animate({
					scrollTop: $(element).offset().top
				}, 800);
			}
		});
	};

	/**
	 * Expand collapse all objects
	 *
	 * @param bool collapse default: false
	 */
	var expandAll = function (collapse)
	{
		$('#hssdoc_sidebar .obj_list').each(function (i, element)
		{
			if (collapse)
			{
				$(element).find('.prop_list').hide();
				$(element).find('a.open').html('[+]');
			}
			else
			{
				$(element).find('.prop_list').show();
				$(element).find('a.open').html('[-]');
			}
		});
	};

	var edit = {
		/**
		 * Get current property id
		 *
		 * @return int
		 */
		get_property_id: function ()
		{
			return (window.location.pathname.match(/^\/edit_property\/(\d+)/) || [])[1] || null;
		},

		/**
		 * Load hssdoc values table data
		 *
		 * @param int property_id
		 * @function callback(object data, mixed error)
		 */
		get_values: function (property_id, callback)
		{
			var cache_key = '/hssdoc/property_values/' + property_id;

			if (typeof callback !== 'function')
			{
				callback = function () {};
			}

			if (App.cache.get(cache_key))
			{
				callback(App.cache.get(cache_key));

				return;
			}

			$.ajax({
				url: App['/shared/hssdoc_url'] + '/property_values.json',
				data: {
					property: property_id
				},
				dataType: 'json',
				success: function (data)
				{
					if (data.status !== 0)
					{
						callback(null, data.error);
						return;
					}

					App.cache.set(cache_key, data.payload);
					callback(data.payload, null);
				},
				error: function (jqxhr, text_status, error)
				{
					callback(null, error);
				}
			});
		},

		/**
		 * Save hssdoc value
		 *
		 * @param object $element
		 * @param function callback(mixed error)
		 */
		save_from_element: function ($element, callback)
		{
			if (typeof callback !== 'function')
			{
				callback = function () {};
			}

			var data = {
				value: $element.find('input[name=value]').val(),
				version: $element.find('input[name=version]').val(),
				'default': $element.find('input[name=default]').is(':checked') ? 1 : 0
			};

			if (isNaN($element.attr('data-id')))
			{
				data.property_id = edit.get_property_id();

				if (data.value.length === 0)
				{
					callback(new App.Error('TooEarlyToSave'));

					return;
				}
			}
			else
			{
				data.id = $element.attr('data-id');
			}

			$.ajax({
				url: App['/shared/hssdoc_url'] + '/property_values.json',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function (data)
				{
					if (data.status !== 0)
					{
						if (data.error === 'ValidationFailed')
						{
							callback(new App.Error('ValidationFailed', {
								validation_errors: data.validation_errors
							}));

							return;
						}

						callback(new App.Error('ResponseError', {
							response_status: data.status,
							response_error: data.error
						}));

						return;
					}

					$element.attr('data-id', data.payload.id);
					callback(null);
				}
			});
		},

		/**
		 * Delete an entry
		 *
		 * @param {integer} id
		 * @param {function} callback
		 */
		delete_item: function (id, callback)
		{
			$.ajax({
				url: App['/shared/hssdoc_url'] + '/property_values.json',
				type: 'DELETE',
				data: {
					id: id
				},
				dataType: 'json',
				success: function (data)
				{
					if (data.status != 0)
					{
						callback(new App.Error('RequestError', {
							request_status: data.status,
							request_error: data.error
						}));

						return;
					}
				},
				error: function (jqxhr, text_status, error)
				{
					callback(new App.Error('RequestError', {
						request_error: text_status,
						error_thrown: error
					}));
				}
			});
		},

		/**
		 * Render a new row
		 *
		 * @param {object} data
		 * @param {function} callback
		 */
		render_new_row: function (data, callback)
		{
			App.data.template('hssdoc_edit_values_row', function (template, error)
			{
				if (error)
				{
					callback(error);
					return;
				}

				if (typeof data !== 'object')
				{
					data = {'new?': true};
				}
				else
				{
					data['default?'] = parseInt(data['default']) === 1;
				}

				$('#hssdoc_add .values_table > tbody > tr.loading').hide();
				$('#hssdoc_add .values_table > tbody')
					.append(Mustache.render(template, data));

				if (typeof callback === 'function')
				{
					callback();
				}
			});
		}
	};

	App.pageEvent.on('load', '/doc', function ()
	{
		// Scroll to the property that's in the hash
		scrollToProperty(window.location.hash.replace(/^#/, ''));

		// Collapse all irrelevant objects on the sidebar
		$('#hssdoc_sidebar .obj_list > li').each(function (i, element)
		{
			if ($(element).attr('data-object') !== getObjectName())
			{
				$(element).find('.prop_list').hide();
				$(element).find('a.open').html('[+]');
			}
		});
	});

	App.pageEvent.on('load_init', '/doc', function ()
	{
		$('#hssdoc_sidebar .obj_list > li a.open').on('click', function (e)
		{
			e.preventDefault();

			var el = $(this).parent().parent().find('.prop_list').toggle();
			$(this).html(el.is(':visible') ? '[-]' : '[+]');
		});

		$('#hssdoc_sidebar .obj_list .prop_list a').on('click', function (e)
		{
			var targetObject = $(this).closest('ul.prop_list')
				.parent().attr('data-object');
			var targetProperty = $(this).attr('href').match(/^.+?#(.+)$/)[1];

			if (targetObject === getObjectName())
			{
				scrollToProperty(targetProperty);
			}
		});

		$('#hssdoc_sidebar .actions a.expand').on('click', function (e)
		{
			e.preventDefault();
			expandAll();
		});

		$('#hssdoc_sidebar .actions a.collapse').on('click', function (e)
		{
			e.preventDefault();
			expandAll(true);
		});
	});

	App.pageEvent.on('load_init', '/doc/edit_property', function ()
	{
		// Auto-save when a values field is edited
		$('#hssdoc_add .values_table > tbody').on('change', 'tr input', function (e)
		{
			var $element = $(this).closest('tr');
			$element
				.addClass('unsaved')
				.find('input').prop('disabled', true);

			edit.save_from_element($element, function (error)
			{
				if (error instanceof App.Error)
				{
					switch (error.name)
					{
						case 'TooEarlyToSave': return;
						case 'ValidationFailed':
							alert(error.validation_errors.join("\n"));
							break;
						default: error.show();
					}

					return;
				}

				$element
					.removeClass('unsaved')
					.find('input').prop('disabled', false);
			});
		});

		// Delete link
		$('#hssdoc_add .values_table').on('click', 'a._delete', function (e)
		{
			e.preventDefault();

			if (!confirm('Are you sure?'))
			{
				return;
			}

			var $element = $(this).closest('tr');
			$element.hide(700);

			if (isNaN($element.attr('data-id')))
			{
				$element.remove();
			}
			else
			{
				edit.delete_item($element.attr('data-id'), function (error)
				{
					if (error instanceof App.Error)
					{
						$element.show();
						error.show();

						return;
					}

					$element.remove();
				});
			}
		});

		// New link
		$('#hssdoc_add table > tfoot > tr.actions a._new_value').on('click', function (e)
		{
			e.preventDefault();
			edit.render_new_row();
		});
	});

	App.pageEvent.on('load', '/doc/edit_property', function ()
	{
		if (edit.get_property_id() === null)
		{
			return;
		}

		edit.get_values(edit.get_property_id(), function (data, error)
		{
			if (data.length === 0)
			{
				$('#hssdoc_add .values_table > tbody > tr.loading').hide();
				return;
			}

			// We request the template here, so the order if values
			// does not get messed up
			App.data.template('hssdoc_edit_values_row', function ()
			{
				// This is needed in order to make sure that the values are
				// rendered in correct order

				var cycle = function (i)
				{
					if (i > data.length - 1)
					{
						return;
					}

					edit.render_new_row(data[i], function ()
					{
						cycle(i + 1);
					});
				};

				cycle(0);
			});
		});
	});
})(window.App);

window['App'].Rsrc.file('js/hssdoc.js').set_loaded();

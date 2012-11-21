window['App'] = window['App'] || {};

(function (App)
{
	/**
	 * Resource manager
	 */
	App.Rsrc = {
		/**
		 * Loaded files
		 *
		 * @private
		 */
		_loaded: {},

		/**
		 * Load multiple resource bundles at once
		 *
		 * @param {Array<string>} bundles
		 * @param {function()} callback
		 */
		loadBundles: function (bundles, callback)
		{
			var f = App.util.multiCallback(bundles.length, callback);

			for (var i = 0, c = bundles.length; i < c; i++)
			{
				if (App.Rsrc.loadBundle(bundles[i], f) === false)
				{
					f();
				}
			}
		},

		/**
		 * Load a resource bundle. In case things go terribly wrong,
		 * false is returned.
		 *
		 * @param {string} bundle_name
		 * @param {function()} callback
		 * @return bool
		 */
		loadBundle: function (bundle_name, callback)
		{
			if (App.rsrc.bundles[bundle_name] === undefined)
			{
				return false;
			}

			if (App.rsrc.prod === true)
			{
				App.Rsrc.loadFile(bundle_name, callback);
				return;
			}

			var bundle = App.rsrc.bundles[bundle_name];
			var f = App.util.multiCallback(bundle.files.length, callback);

			for (var i = 0, c = bundle.files.length; i < c; i++)
			{
				App.Rsrc.loadFile(bundle.files[i], f);
			}
		},

		/**
		 * Load a CSS or JS file.
		 *
		 * @todo when in production mode, try to find a bundle to load, instead
		 * @param {string} file
		 * @param {function()} callback
		 */
		loadFile: function (file, callback)
		{
			if (typeof callback !== 'function')
			{
				callback = function () {};
			}

			var extension = file.split('.').pop();
			var url = /^https?:\/\//.test(file) ? file :
				App.rsrc.root + '/' + file;

			if (App.Rsrc._loaded[url] === true)
			{
				callback();
				return;
			}

			if (extension === 'css')
			{
				// TODO Load qith AJAX
				$('head').append($('<link>')
					.attr('rel', 'stylesheet')
					.attr('type', 'text/css')
					.attr('href', url));

				App.Rsrc._loaded[url] = true;
				callback();
			}
			else if (extension === 'js')
			{
				$.ajax({
					url: url,
					dataType: 'script',
					timeout: 7000,
					complete: function (jqXHR, textStatus)
					{
						App.Rsrc._loaded[url] = true;
						callback();
					}
				});
			}
			else
			{
				callback();
			}
		}
	};
})(window['App']);

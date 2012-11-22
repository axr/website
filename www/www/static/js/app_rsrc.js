window['App'] = window['App'] || {};

(function (App)
{
	/**
	 * Resource manager
	 */
	App.Rsrc = {
		/**
		 * Bundles
		 *
		 * @private
		 */
		_bundles: {},

		/**
		 * Files
		 *
		 * @private
		 */
		_files: {},

		/**
		 * Bundle
		 */
		Bundle: function (name)
		{
			var that = this;

			var STATUS_NONE = 0;
			var STATUS_LOADING = 1;
			var STATUS_LOADED = 2;
			var STATUS_ERROR = 3;

			/**
			 * Bundle name
			 *
			 * @type {string}
			 */
			this.name = name;

			/**
			 * Bundle status
			 *
			 * @type {number}
			 */
			this.status = STATUS_NONE;

			/**
			 * Bundle info
			 */
			this.info = App.rsrc.bundles[this.name] || {
				type: null,
				files: []
			};

			/**
			 * All registered callbacks
			 *
			 * @type {Array<function(App.Error)>}
			 */
			this._callbacks = [];

			/**
			 * Load the bundle
			 */
			this._load = function ()
			{
				if (this.status !== STATUS_NONE)
				{
					return;
				}

				var callback = function (error)
				{
					that.status = (error instanceof App.Error) ? STATUS_ERROR : STATUS_LOADED;
					that._call_callbacks(error);
				};

				if (App.rsrc.prod === true)
				{
					App.Rsrc.file(this.name).with(callback);
				}
				else
				{
					var f = App.util.multiCallback(this.info.files.length, callback);
					for (var i = 0, c = this.info.files.length; i < c; i++)
					{
						App.Rsrc.file(this.info.files[i]).with(f);
					}
				}
			};

			/**
			 * Call all callbacks
			 *
			 * @params {App.Error} error
			 */
			this._call_callbacks = function (error)
			{
				for (var i = 0, c = this._callbacks.length; i < c; i++)
				{
					this._callbacks[i](error);
				}
			};

			/**
			 * Set a callback
			 *
			 * @param {function} callback
			 */
			this.with = function (callback)
			{
				if (typeof callback !== 'function')
				{
					return;
				}

				if (this.status === STATUS_LOADED)
				{
					callback();
					return;
				}

				this._callbacks.push(callback);
			};

			this._load();
		},

		/**
		 * File
		 */
		File: function (file)
		{
			var that = this;

			var STATUS_NONE = 0;
			var STATUS_LOADING = 1;
			var STATUS_LOADED = 2;
			var STATUS_ERROR = 3;

			/**
			 * File name
			 *
			 * @type {string}
			 */
			this.file = file;

			/**
			 * File status
			 *
			 * @type {number}
			 */
			this.status = STATUS_NONE;

			/**
			 * All registered callbacks
			 *
			 * @type {Array<function(App.Error)>}
			 */
			this._callbacks = [];

			/**
			 * Load the file
			 */
			this._load = function ()
			{
				if (this.status !== STATUS_NONE)
				{
					return;
				};

				this.status = STATUS_LOADING;

				var extension = file.split('.').pop();
				var url = /^https?:\/\//.test(this.file) ? this.file :
					App.rsrc.root + '/' + this.file;

				if (extension === 'css')
				{
					// TODO: Load with AJAX
					$('head').append($('<link>')
						.attr('rel', 'stylesheet')
						.attr('type', 'text/css')
						.attr('href', url));

					that.set_loaded();
					that._call_callbacks(null);
				}
				else if (extension === 'js')
				{
					$.ajax({
						cache: false,
						url: url,
						dataType: 'script',
						timeout: 7000,
						complete: function (jqXHR, textStatus)
						{
							that.set_loaded();
							that._call_callbacks(null);
						}
					});
				}
			};

			/**
			 * Call all callbacks
			 *
			 * @params {App.Error} error
			 */
			this._call_callbacks = function (error)
			{
				for (var i = 0, c = this._callbacks.length; i < c; i++)
				{
					this._callbacks[i](error);
				}
			};

			/**
			 * Set the file as loaded. This will call no callbacks.
			 */
			this.set_loaded = function ()
			{
				that.status = STATUS_LOADED;
			};

			/**
			 * Set a callback
			 *
			 * @param {function} callback
			 */
			this.with = function (callback)
			{
				if (typeof callback !== 'function')
				{
					return;
				}

				if (this.status == STATUS_LOADED)
				{
					callback();
					return;
				}

				this._callbacks.push(callback);
			};

			this._load();
		},

		/**
		 * This will return an App.Rsrc.Bundle instance
		 */
		bundle: function (bundle)
		{
			if (!(App.Rsrc._bundles[bundle] instanceof App.Rsrc.Bundle))
			{
				App.Rsrc._bundles[bundle] = new App.Rsrc.Bundle(bundle);
			}

			return App.Rsrc._bundles[bundle];
		},

		/**
		 * This will return an App.Rsrc.File instance
		 */
		file: function (file)
		{
			if (!(App.Rsrc._files[file] instanceof App.Rsrc.File))
			{
				App.Rsrc._files[file] = new App.Rsrc.File(file);
			}

			return App.Rsrc._files[file];
		},

		/**
		 * Load multiple resource bundles at once
		 *
		 * @deprecated
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
		 * @deprecated
		 * @param {string} bundle_name
		 * @param {function()} callback
		 * @return bool
		 */
		loadBundle: function (bundle_name, callback)
		{
			App.Rsrc.bundle(bundle_name).with(callback);
		},

		/**
		 * Load a CSS or JS file.
		 *
		 * @deprecated
		 * @todo when in production mode, try to find a bundle to load, instead
		 * @param {string} file
		 * @param {function()} callback
		 */
		loadFile: function (file, callback)
		{
			App.Rsrc.get_file(file).with(callback);
		}
	};
})(window['App']);

window['Rsrc'] = {};

(function (Rsrc)
{
	Rsrc.STATUS_NONE = 0;
	Rsrc.STATUS_LOADING = 1;
	Rsrc.STATUS_LOADED = 2;
	Rsrc.STATUS_ERROR = 3;

	Rsrc._file_instances = {};

	Rsrc.File = function (file)
	{
		var that = this;
		var prototype = Rsrc.File.prototype;

		{
			prototype._instances = prototype._instances || {};

			if (prototype._instances[file] !== undefined)
			{
				return prototype._instances[file];
			}

			prototype._instances[file] = this;
		}

		/**
		 * Name of the requested file
		 *
		 * @type {string}
		 */
		this.file = file;

		/**
		 * Status
		 *
		 * @type {number}
		 */
		this.status = Rsrc.STATUS_NONE;

		/**
		 * All registered callbacks
		 *
		 * @type {Array<function(App.Error)>}
		 */
		this._callbacks = [];

		/**
		 * Call all registered callbacks
		 */
		this._call_callbacks = function (error)
		{
			for (var i = 0, c = this._callbacks.length; i < c; i++)
			{
				if (typeof this._callbacks[i] === 'function')
				{
					this._callbacks[i](error || null);
					this._callbacks[i] = null;
				}
			}
		};

		/**
		 * Provide the requested contents.
		 *
		 * @param {function} providor
		 */
		this.provide = function (providor)
		{
			if (this.status === Rsrc.STATUS_NONE ||
				this.status === Rsrc.STATUS_LOADING)
			{
				providor();

				this.status = Rsrc.STATUS_LOADED;
				this._call_callbacks();
			}
		};

		/**
		 * Request the file to be loaded
		 *
		 * @param {function} callback
		 */
		this.request = function (callback)
		{
			switch (this.status)
			{
				case Rsrc.STATUS_LOADED:
					callback();
					break;

				case Rsrc.STATUS_ERROR:
					callback(new Error('Rsrc.LoadError', {
						name: this.name
					}));
					break;

				default:
					this._callbacks.push(callback);
			}

			if (this.status !== Rsrc.STATUS_NONE)
			{
				return;
			}

			this.status = Rsrc.STATUS_LOADING;

			var type = file.split('.').pop();
			var url = /^https?:\/\//.test(file) ? file : App.vars.rsrc_root + '/' + file;

			if (type === 'css')
			{
				$('head').append($('<link>')
					.attr('rel', 'stylesheet')
					.attr('type', 'text/css')
					.attr('href', url));

				this.status = Rsrc.STATUS_LOADED;
				this._call_callbacks(null);
			}
			else if (type === 'js')
			{
				$('body').append($('<script>').attr('src', url));

				setTimeout(function ()
				{
					if (that.status !== Rsrc.STATUS_LOADED)
					{
						this._call_callbacks(new Error('Rsrc.LoadError', {
							name: that.name
						}));
					}
				}, 7000);
			}
		};
	};

	/**
	 * Call the callback, when all the required files are available
	 *
	 * @param {Array<string>} files
	 * @param {function} callback
	 */
	Rsrc.use = function (files, callback)
	{
		var mc_count = files.length;
		var mc = function ()
		{
			if (--mc_count === 0 && typeof callback === 'function')
			{
				callback();
			}
		};

		for (var i = 0, c = files.length; i < c; i++)
		{
			(new Rsrc.File(files[i])).request(mc);
		}
	};
})(window['Rsrc']);

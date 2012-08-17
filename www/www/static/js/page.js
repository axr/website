(function (Ajaxsite)
{
	Ajaxsite.handlers.page = function (data)
	{
		var that = this;

		/**
		 * Data
		 *
		 * @var object
		 */
		this.data = data;

		/**
		 * Has the page been initialized (event handlers bound)
		 *
		 * @var bool
		 */
		this.isInitialized = false;

		/**
		 * Block that holds the page HTML
		 *
		 * @var Ajaxsite.Block
		 */
		this.pageBlock = new Ajaxsite.Block();

		/**
		 * Block that holds the breadcrumb
		 *
		 * @var Ajaxsite.Block
		 */
		this.breadcrumbBlock = new Ajaxsite.Block();

		/**
		 * Block that holds the tabs
		 *
		 * @var Ajaxsite.Block
		 */
		this.tabsBlock = new Ajaxsite.Block();

		/**
		 * Render page that has ctype `bpost`
		 *
		 * @param object page
		 */
		this.renderPage = function (data)
		{
			var breadcrumb = [
				{ name: 'Home', link: '/' }
			];

			if (data.page.ctype === 'bpost')
			{
				breadcrumb.push({ name: 'Blog', link: '/blog' });
			}

			breadcrumb.push({ name: data.page.title });

			Ajaxsite.renderBreadcrumb(breadcrumb, function (html, error)
			{
				if (error)
				{
					return;
				}

				that.breadcrumbBlock.html(html);
			});

			if (data.can_edit === true)
			{
				Ajaxsite.renderTabs([
					{
						name: 'View',
						link: that.data._url,
						current: true
					},
					{
						name: 'Edit',
						link: '/page/' + data.page.id + '/edit'
					}
				], function (html, error)
				{
					if (error)
					{
						return;
					}

					that.tabsBlock.html(html);
				});
			}

			Ajaxsite.data.template('page_page', function (template, error)
			{
				if (error)
				{
					Ajaxsite.error('Error loading template page_bpost: ' + error, true);
					return;
				}

				that.pageBlock.html(Mustache.render(template, {
					_tabs_html: that.tabsBlock.placeholder(),
					_breadcrumb_html: that.breadcrumbBlock.placeholder(),
					_ajax: true,
					page: data.page
				}));
			});
		};

		if (!this.data._prerendered)
		{
			this.pageBlock.html(Ajaxsite.renderLoading());

			Ajaxsite.data.page(data._url, function (data, error)
			{
				if (error)
				{
					Ajaxsite.error('Error loading page ' + data._url +
						':' + error, true);
					return;
				}

				that.renderPage(data);
			});

			Ajaxsite.content.html(this.pageBlock.placeholder());
		}
	};
})(Ajaxsite);


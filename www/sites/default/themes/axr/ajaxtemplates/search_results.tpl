<nav id="breadcrumb">
	<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="/" itemprop="url">
			<span itemprop="title">Home</span></a>
	</div>
	<span class="extra_0"></span>
	<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
		<a href="/search" itemprop="url">
			<span itemprop="title">Search</span>
		</a>
	</div>
	<span class="extra_0"></span>
	<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
		<span class="current" itemprop="title">Results for "{{query}}"</span>
	</div>
</nav>

<div class="search_results">
	{{#results}}
		<a class="title" href="{{link}}">{{title}}</a>
		<p>{{{snippet}}}</p>
	{{/results}}
	{{#no_results}}
		<p>There were no matches for query "{{query}}".</p>
	{{/no_results}}
</div>


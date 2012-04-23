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

<div id="search" class="search_results">
	<div class="sidebar">
		<div class="advanced">{{{advanced_block}}}</div>
		<div class="separator">
			<div class="extra_0"></div>
			<div class="extra_1"></div>
		</div>
	</div>
	<div class="results">
		<h2>Search results:</h2>
		{{#results}}
			<div class="item">
				<a class="title" href="{{link}}">{{title}}</a>
				<div class="info">
					{{#author}}
						<a class="author" href="{{author_link}}">{{author}}</a> -
					{{/author}}
					<span class="date">{{date}}</span> -
					<span class="time">{{time}}</span>
				</div>
				<p>{{{snippet}}}</p>
			</div>
		{{/results}}
		{{#no_results}}
			<div class="nothing">
				<p>There were no matches for query "{{query}}".</p>
			</div>
		{{/no_results}}
	</div>
</div>


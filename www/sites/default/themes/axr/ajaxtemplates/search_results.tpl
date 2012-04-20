<div class="search_results">
	{{#results}}
		<a class="title" href="{{link}}">{{title}}</a>
		<p>{{{snippet}}}</p>
	{{/results}}
	{{#no_results}}
		<p>There were no matches for query "{{query}}".</p>
	{{/no_results}}
</div>


<div class="search_results">
	{{#results}}
		<a class="title" href="{{link}}">{{node.title}}</a>
		<p>{{{snippet}}}</p>
	{{/results}}
	{{#no_results}}
		<p>There were no matches for query "{{query}}".</p>
	{{/no_results}}
</div>


<div class="search_results">
	{{#results}}
		<div class="item">
			<div class="left">
				<span class="type">{{type_str}}</span>
				<span class="date">{{date}} - {{time}}</span>
			</div>
			<div class="right">
				<a class="title" href="{{link}}">{{title}}</a>
				<p>{{{snippet}}}</p>
			</div>
		</div>
	{{/results}}
	{{#no_results}}
		<div class="nothing">
			<p>There were no matches for query "{{query}}".</p>
		</div>
	{{/no_results}}
</div>


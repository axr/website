<div class="search_results">
	{{#results}}
		<div class="item">
			<div class="left">
				<span class="type">{{type_str}}</span>
				{{#date}}<span class="date">{{date}}</span>{{/date}}
			</div>
			<div class="right">
				<a class="title" href="{{link}}">{{title}}</a>
				{{#snippet}}<p>{{{snippet}}}</p>{{/snippet}}
			</div>
		</div>
	{{/results}}
	{{#no_results}}
		<div class="nothing">
			<p>There were no matches for query "{{query}}".</p>
		</div>
	{{/no_results}}
</div>


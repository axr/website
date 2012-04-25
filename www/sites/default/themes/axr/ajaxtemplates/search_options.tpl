<div class="search_options_ghost">
	<div class="search_options clearfix">
		<form class="search" method="post" action="/search/mixed">
			<input type="search" placeholder="Search" value="{{query}}" />
		</form>

		<div class="options">
			<label for="type">What to search?</label>
			<select class="type" name="type">
				{{#types}}
					<option value="{{type}}"{{#selected}} selected="selected"{{/selected}}>{{name}}</option>
				{{/types}}
			</select>
		</div>
	</div>
</div>


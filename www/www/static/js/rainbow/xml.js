window['App'].Rsrc.file('js/rainbow/rainbow.js').use(function ()
{
	Rainbow.extend('xml', [
		 {
			'name': 'comment',
			'pattern': /&lt;\!--[\S\s]*?--&gt;/g
		},
		{
			'matches': {
				1: 'support.tag.open',
				2: 'support.tag.close'
			},
			'pattern': /(&lt;)|(\/?\??&gt;)/g
		},
		{
			'name': 'support.tag',
			'matches': {
				1: 'support.tag',
				2: 'support.tag.special',
				3: 'support.tag-name'
			},
			'pattern': /(&lt;\??)(\/|\!?)(\w+)/g
		},
		{
			'matches': {
				1: 'support.attribute'
			},
			'pattern': /([a-z-]+)(?=\=)/g
		},
		{
			'matches': {
				2: 'string'
			},
			'pattern': /=('.*?'|".*?")/g
		},
		{
			'matches': {
				1: 'support.attribute'
			},
			'pattern': /\s(\w+)(?=\s|&gt;)(?![\s\S]*&lt;)/g
		}
	], true);
});

window['App'].Rsrc.file('js/rainbow/xml.js').set_loaded();

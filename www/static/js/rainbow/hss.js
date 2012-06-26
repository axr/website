/**
 * HSS patterns
 *
 * @author Ragnis Armus
 * @version 1.0.0
 */

var general = [
	{
		'name': 'comment',
		'pattern': /(\/\/.*|\/\*.*\*\/)/g
	},
	{
		'name': 'constant.hex-color',
		'pattern': /#([a-f0-9]{8}|[a-f0-9]{3,4}|[a-f0-9])/gi
	},
	{
		'name': 'constant.numeric',
		'pattern': /(\d+)(%|px)?/g
	},
	{
		'name': 'string',
		'pattern': /".*?"/g
	},
	{
		'matches': {
			1: 'hss-object-type',
			2: 'hss-object-name'
		},
		'pattern': /(@[a-zA-Z]+)(\s+([a-zA-Z0-9]+))?/g
	},
	{
		'name': 'hss-property',
		'pattern': /\w+:\s/g
	},
	{
		'name': 'hss-instruction',
		'pattern': /#(ensure|wrap|new|move|delete|filter|support)/g
	},
	{
		'matches': {
			1: 'hss-function'
		},
		'pattern': /(min|max|ref)\(/g
	},
	{
		'matches': {
			3: 'hss-selector',
			4: [
				{
					'matches': {
						1: 'hss-property',
						2: 'constant.numeric'
					},
					'pattern': /\((\w+) > ([0-9]+)/g
				},
				/*{
					'matches': {
						1: 'hss-property'
					},
					'pattern': /\((\w+)/g
				}*/
			]
		},
		//'pattern': /(^\s*|\[\s*|([+\-=>]|\.\.)\s*|\s+)(\w+)/gm
		'pattern': /(^\s*|\[\s*|([+\-=>]|\.\.)\s*|\s+)(\w+)(\(.+\))?/gm
	}
];

Rainbow.extend('hss', [
	{
		'matches': {
			1: 'hss-function',
			2: general.concat([
				{
					'matches': {
						1: 'hss-property',
						2: [
							{
								'name': 'hss-object-name',
								'pattern': /@\w+/g
							},
							{
								'name': 'hss-selector',
								'pattern': /(\w+|\*)/g
							}
						]
					},
					'pattern': /^(\w+) of (.+)?$/g
				},
				{
					'name': 'hss-property',
					'pattern': /^\w+$/g
				}
			])
		},
		'pattern': /(min|max|ref)\((.+?)\)/g
	}
], true);

var filter = {
	'name': 'hss-filter',
	'matches': {
		2: general
	},
	'pattern': /:\w+(\((.+?)\))?/g
};
general.push(filter);

Rainbow.extend('hss', general, true);
Rainbow.extend('hss', [
	{
		'name': 'hss-filter',
		'matches': {
			1: general
		},
		'pattern': /:\[(.+?)\]/g
	}
], true);


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
			1: 'hss-property'
		},
		'pattern': /(\w+):\s*/gi
	},
	{
		'matches': {
			1: 'hss-property',
			2: 'hss-keyword'
		},
		'pattern': /(\w+):\s*([a-z]+)\s*;/gi
	},
	{
		'matches': {
			1: 'hss-object-type',
			2: 'hss-object-name'
		},
		'pattern': /(@[a-z]+)(\s+([a-z0-9]+))?/gi
	},
	{
		'name': 'hss-instruction',
		'pattern': /#[a-z]+/gi
	},
	{
		'matches': {
			1: 'hss-instruction',
			2: [
				{
					'name': 'hss-selector',
					'pattern': /[a-z0-9_]+/gi
				}
			]
		},
		'pattern': /(#[a-z]+)\((.+?)\)/gi
	},
	{
		'matches': {
			1: 'hss-function'
		},
		'pattern': /(min|max|ref)\(/g
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
								'pattern': /([a-z0-9_]+|\*)/gi
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
	},
	{
		'matches': {
			3: 'hss-selector',
			4: [
				{
					'matches': {
						1: 'hss-property',
						3: 'constant.numeric'
					},
					'pattern': /\((\w+)\s*(&lt;|&gt;|[=])\s*([0-9]+)/g
				}
			]
		},
		'pattern': /(^\s*|\[\s*|([+\-=>]|\.\.|)\s*)([a-z0-9_]+)(\(.+\))?(\s+|\s*\{|$)/gmi
	}
	/*{
		'matches': {
			1: 'hss-selector',
			2: 'hss-property',
			3: 'constant.numeric'
		},
		'pattern': /(\w+)\((\w+) +\> +(.+?)\)/g
	}*/
], true);

var filter = {
	'name': 'hss-filter',
	'matches': {
		1: 'hss-selector',
		3: general
	},
	'pattern': /([a-z0-9_]+):\w+(\((.+?)\))?/gi
};
general.push(filter);

Rainbow.extend('hss', general, true);
Rainbow.extend('hss', [
	{
		'name': 'hss-filter',
		'matches': {
			1: 'hss-selector',
			2: 'hss-selector',
			3: general
		},
		'pattern': /([a-z0-9_]+):\[([a-z0-9_]+)(.+?)\]/gi
	}
], true);


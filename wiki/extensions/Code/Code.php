<?php

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => '&lt;code&gt;',
	'description' => 'Make MW parse &lt;code&gt; tags correctly',
	'author' => 'Ragnis Armus',
	'url' => 'http://github.com/AXR/Website',
);

$wgHooks['ParserAfterTidy'][] = 'Code::hook_ParserAfterTidy';
$wgHooks['ParserFirstCallInit'][] = 'Code::hook_ParserFirstCallInit';

class Code
{
	/**
	 * Callback for hook ParserFirstCallInit
	 */
	public static function hook_ParserFirstCallInit (Parser &$parser)
	{
		$parser->setHook('code', 'Code::render');
		return true;
	}

	/**
	 * Callback for hook ParserAfterTidy
	 */
	public static function hook_ParserAfterTidy (Parser &$parser, &$text)
	{
		preg_match_all('/<code.+?>(.+?)<\/code>/s', $text, $match);

		foreach ($match[1] as $block)
		{
			$text = str_replace($block, preg_replace('/^[\t\n]/m', '', $block), $text);
		}

		return false;
	}

	/**
	 * Render <code> tags
	 */
	public static function render ($input, array $args,
		Parser $parser, PPFrame $frame)
	{
		$lines = explode("\n", $input);
		$args_str = ' ';

		foreach ($lines as &$line)
		{
			$line = '	' . $line;
		}

		foreach ($args as $key => $value)
		{
			$args_str .= $key . '="' . str_replace('"', '', $value) . '"';
		}

		return "<code{$args_str}>" . implode("\n", $lines) . '</code>';
	}
}


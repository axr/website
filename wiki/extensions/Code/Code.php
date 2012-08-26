<?php

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'No <pre> in <code>',
	'description' => 'Remove <pre> tags from <code>',
	'version' => 1, 
	'author' => 'Ragnis Armus',
	'url' => 'http://github.com/AXR/Website',
);

$wgHooks['ParserAfterTidy'][] = 'NoPreInCode::hook_ParserAfterTidy';

$wgHooks['ParserFirstCallInit'][] = 'wfSampleParserInit';
 
// Hook our callback function into the parser
function wfSampleParserInit (Parser $parser)
{
        // When the parser sees the <sample> tag, it executes 
        // the wfSampleRender function (see below)
        $parser->setHook( 'code', 'wfSampleRender' );
        // Always return true from this function. The return value does not denote
        // success or otherwise have meaning - it just must always be true.
        return true;
}
 
// Execute 
function wfSampleRender ($input, array $args, Parser $parser, PPFrame $frame)
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

class NoPreInCode
{
	/**
	 * Callback for hook ParserAfterTidy
	 */
	public static function hook_ParserAfterTidy (&$parser, &$text)
	{
		preg_match_all('/<code.+?>(.+?)<\/code>/s', $text, $match);

		foreach ($match[1] as $block)
		{
			$text = str_replace($block, preg_replace('/^[\t\n]/m', '', $block), $text);
		}

		return false;
	}
}


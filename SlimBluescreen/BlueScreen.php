<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace SlimBluescreen;

/**
 * Red BlueScreen.
 *
 * @author     David Grudl
 */
class BlueScreen
{
	/** @var array */
	protected $panels = array();

	/** @var string[] paths to be collapsed in stack trace (e.g. core libraries) */
	protected $collapsePaths = array();

	protected $time;
	protected $source;

	public static $editor = 'editor://open/?file=%file&line=%line';

	protected static $errorTypes = array(
		E_ERROR => 'Fatal Error',
		E_USER_ERROR => 'User Error',
		E_RECOVERABLE_ERROR => 'Recoverable Error',
		E_CORE_ERROR => 'Core Error',
		E_COMPILE_ERROR => 'Compile Error',
		E_PARSE => 'Parse Error',
		E_WARNING => 'Warning',
		E_CORE_WARNING => 'Core Warning',
		E_COMPILE_WARNING => 'Compile Warning',
		E_USER_WARNING => 'User Warning',
		E_NOTICE => 'Notice',
		E_USER_NOTICE => 'User Notice',
		E_STRICT => 'Strict standards',
		E_DEPRECATED => 'Deprecated',
		E_USER_DEPRECATED => 'User Deprecated',
	);

	public function __construct()
	{
		$this->time = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(TRUE);
		if (isset($_SERVER['REQUEST_URI'])) {
			$this->source = (isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off') ? 'https://' : 'http://')
				. (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')
				. $_SERVER['REQUEST_URI'];
		} else {
			$this->source = empty($_SERVER['argv']) ? 'CLI' : 'CLI: ' . implode(' ', $_SERVER['argv']);
		}
	}

	/**
	 * Add custom panel.
	 * @param  callable
	 * @return self
	 */
	public function addPanel($panel)
	{
		if (!in_array($panel, $this->panels, TRUE)) {
			$this->panels[] = $panel;
		}
		return $this;
	}

	/**
	 * Renders blue screen.
	 * @param  \Exception
	 * @return string
	 */
	public function render(\Exception $exception)
	{
		$panels = $this->panels;

		ob_start();
		require __DIR__ . '/bluescreen.phtml';
		return ob_get_clean();
	}

	/**
	 * Returns syntax highlighted source code.
	 * @param  string
	 * @param  int
	 * @param  int
	 * @return string
	 */
	protected static function highlightFile($file, $line, $lines = 15, $vars = array())
	{
		$source = @file_get_contents($file); // intentionally @
		if ($source) {
			return substr_replace(
				static::highlightPhp($source, $line, $lines, $vars),
				' data-nette-href="' . htmlspecialchars(strtr(static::$editor, array('%file' => rawurlencode($file), '%line' => $line))) . '"',
				4, 0
			);
		}
	}

	/**
	 * Returns syntax highlighted source code.
	 * @param  string
	 * @param  int
	 * @param  int
	 * @return string
	 */
	protected static function highlightPhp($source, $line, $lines = 15, $vars = array())
	{
		if (function_exists('ini_set')) {
			ini_set('highlight.comment', '#998; font-style: italic');
			ini_set('highlight.default', '#000');
			ini_set('highlight.html', '#06B');
			ini_set('highlight.keyword', '#D24; font-weight: bold');
			ini_set('highlight.string', '#080');
		}

		$source = str_replace(array("\r\n", "\r"), "\n", $source);
		$source = explode("\n", highlight_string($source, TRUE));
		$out = $source[0]; // <code><span color=highlight.html>
		$source = str_replace('<br />', "\n", $source[1]);

		$out .= static::highlightLine($source, $line, $lines);
		$out = preg_replace_callback('#">\$(\w+)(&nbsp;)?</span>#', function($m) use ($vars) {
			return isset($vars[$m[1]])
				? '" title="' . str_replace('"', '&quot;', strip_tags(static::dumpToHtml($vars[$m[1]]))) . $m[0]
				: $m[0];
		}, $out);

		return "<pre class='php'><div>$out</div></pre>";
	}

	/**
	 * Returns highlighted line in HTML code.
	 * @return string
	 */
	protected static function highlightLine($html, $line, $lines = 15)
	{
		$source = explode("\n", "\n" . str_replace("\r\n", "\n", $html));
		$out = '';
		$spans = 1;
		$start = $i = max(1, $line - floor($lines * 2/3));
		while (--$i >= 1) { // find last highlighted block
			if (preg_match('#.*(</?span[^>]*>)#', $source[$i], $m)) {
				if ($m[1] !== '</span>') {
					$spans++;
					$out .= $m[1];
				}
				break;
			}
		}

		$source = array_slice($source, $start, $lines, TRUE);
		end($source);
		$numWidth = strlen((string) key($source));

		foreach ($source as $n => $s) {
			$spans += substr_count($s, '<span') - substr_count($s, '</span');
			$s = str_replace(array("\r", "\n"), array('', ''), $s);
			preg_match_all('#<[^>]+>#', $s, $tags);
			if ($n == $line) {
				$out .= sprintf(
					"<span class='highlight'>%{$numWidth}s:    %s\n</span>%s",
					$n,
					strip_tags($s),
					implode('', $tags[0])
				);
			} else {
				$out .= sprintf("<span class='line'>%{$numWidth}s:</span>    %s\n", $n, $s);
			}
		}
		$out .= str_repeat('</span>', $spans) . '</code>';
		return $out;
	}

	/**
	 * Should a file be collapsed in stack trace?
	 * @param string $file
	 * @return bool
	 */
	protected function isCollapsed($file)
	{
		foreach ($this->collapsePaths as $path) {
			if (strpos(strtr($file, '\\', '/'), strtr("$path/", '\\', '/')) === 0) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Returns link to editor.
	 * @param string $file
	 * @param int $line
	 * @return string
	 */
	protected static function editorLink($file, $line)
	{
		if (static::$editor && is_file($file)) {
			$dir = dirname(strtr($file, '/', DIRECTORY_SEPARATOR));
			$base = isset($_SERVER['SCRIPT_FILENAME']) ? dirname(dirname(strtr($_SERVER['SCRIPT_FILENAME'], '/', DIRECTORY_SEPARATOR))) : dirname($dir);
			if (substr($dir, 0, strlen($base)) === $base) {
				$dir = '...' . substr($dir, strlen($base));
			}
			$href = strtr(static::$editor, array('%file' => rawurlencode($file), '%line' => $line));
			$title = "$file:$line";
			$html = htmlSpecialChars(rtrim($dir, DIRECTORY_SEPARATOR), ENT_IGNORE) . DIRECTORY_SEPARATOR . '<b>' . htmlSpecialChars(basename($file), ENT_IGNORE) . '</b>' . ($line ? ":$line" : '');
			return '<a href="' . htmlspecialchars($href) . '" title="' . htmlspecialchars($title) . '">' . $html . '</a>';
		} else {
			return '<span>' . ($file . ($line ? ":$line" : '')) . '</span>';
		}
	}
}
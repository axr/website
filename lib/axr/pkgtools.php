<?php

namespace AXR;

class Pkgtools
{
	/**
	 * Detect client's OS
	 *
	 * @return string (osx|linux|windows)
	 */
	public static function detect_os ()
	{
		if (preg_match('/Mac/', $_SERVER['HTTP_USER_AGENT']))
		{
			return 'osx';
		}

		if (preg_match('/Linux/', $_SERVER['HTTP_USER_AGENT']))
		{
			return 'linux';
		}

		return 'windows';
	}

	/**
	 * Detect client's system architecture
	 *
	 * @return string (intel|x86_64|i386)
	 */
	public static function detect_arch ()
	{
		if (self::detect_os() === 'osx')
		{
			return 'intel';
		}

		if (preg_match('/wow64|x86_64|x86-64|x64|amd64/i', $_SERVER['HTTP_USER_AGENT']))
		{
			return 'x86_64';
		}

		return 'i386';
	}

	/**
	 * Detect the client's Linux distribution
	 *
	 * @return string
	 */
	public static function detect_linux_distro ()
	{
		if (self::detect_os() !== 'linux')
		{
			return null;
		}

		if (preg_match('/(ubuntu|fedora|red hat|gentoo|suse|centos)/i', $_SERVER['HTTP_USER_AGENT'], $match))
		{
			return str_replace(' ', '', $match[1]);
		}

		return null;
	}

	/**
	 * Get the client's package manager's preferred extension. If no match is
	 * found, `tar.gz` is returned
	 *
	 * @return string
	 */
	public static function get_pm_ext ()
	{
		$distro = self::detect_linux_distro();

		if ($distro === null)
		{
			return null;
		}

		$distro_to_ext = array(
			'ubuntu' => 'deb',
			'fedora' => 'rpm',
			'redhat' => 'rpm'
		);

		return isset($distro_to_ext[$distro]) ?
			$distro_to_ext[$distro] : 'tar.gz';
	}

	/**
	 * Convert the internally used OS identifier to a human-readable OS name
	 *
	 * @param string $os
	 * @return string
	 */
	public static function os_to_human ($os)
	{
		$oses = array(
			'linux' => 'Linux',
			'osx' => 'OS X',
			'windows' => 'Windows'
		);

		return isset($oses[$os]) ? $oses[$os] : $os;
	}

	/**
	 * Convert the internally used distribution identifier to a human-readable
	 * distribution name
	 *
	 * @param string $distro
	 * @return string
	 */
	public static function distro_to_human ($distro)
	{
		$distros = array(
			'ubuntu' => 'Ubuntu',
			'fedora' => 'Fedora',
			'redhat' => 'Red Hat'
		);

		return isset($distros[$distro]) ? $distros[$distro] : $distro;
	}

	/**
	 * Compare two version numbers.
	 *
	 * Return values:
	 * - `-1` $a < $b
	 * - `0` $a = $b
	 * - `1` $a > $b
	 *
	 * @param string $a
	 * @param string $b
	 * @return int
	 */
	public static function compare_versions ($a, $b)
	{
		$a_comp = explode('.', $a);
		$b_comp = explode('.', $b);

		for ($i = 0; $i < 4; $i++)
		{
			$a0 = isset($a_comp[$i]) ? (int) $a_comp[$i] : 0;
			$b0 = isset($b_comp[$i]) ? (int) $b_comp[$i] : 0;

			if ($a0 !== $b0)
			{
				return $a0 > $b0 ? 1 : -1;
			}
		}

		return 0;
	}

	public static function test_arch_compatibility ($base, $b)
	{
		if ($base === 'intel')
		{
			// Client is running OS X, let's assume that it's compatible with
			// everything.
			return true;
		}

		return ($base === $b || $base === 'x86_64');
	}
}

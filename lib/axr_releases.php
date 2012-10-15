<?php

class AXRReleases
{
	/**
	 * Repository name in format USER/REPOSITORY
	 * @var string
	 */
	private $repository;

	/**
	 * Constructor
	 *
	 * @pparam string $repository
	 */
	public function __construct ($repository)
	{
		$this->repository = $repository;
	}

	/**
	 * Get parsed releases data
	 *
	 * @return mixed
	 */
	public function get_releases ()
	{
		$out = Cache::get('/axr_releases/:repo/' . $this->repository);

		if (!is_object($out))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' .
				$this->repository . '/downloads');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

			$data = json_decode(curl_exec($ch));
			curl_close($ch);

			if (!is_array($data))
			{
				return array();
			}

			$out = new StdClass();

			for ($i = 0, $c = count($data); $i < $c; $i++)
			{
				$item = $data[$i];

				preg_match('/^axr-(?<version>[0-9.]+)((-git(?<sha>[0-9a-f]+))?-(?<os>(linux|osx|windows|src)))?(-(?<arch>(x86_64|i386|arm)))?\.(?<ext>[a-z0-9.]+)$/i', $item->name, $match);

				if (!is_array($match) || count($match) === 0)
				{
					continue;
				}

				$version = $match['version'];
				$sha = !empty($match['sha']) ? $match['sha'] : null;
				$os = !empty($match['os']) ? $match['os'] : null;
				$arch = $match['arch'];
				$ext = $match['ext'];

				$type = $sha === null ? 'stable' : 'dev';

				if ($type === 'dev')
				{
					$version = $version . '-git' . $sha;
				}

				if ($os === null)
				{
					// Detect the OS by file extension
					switch ($ext)
					{
						case 'rpm':
						case 'deb': $os = 'linux'; break;
						default: continue(2);
					}
				}

				if ($os === 'android' || $os === 'ios')
				{
					continue;
				}

				if (!isset($out->{$version}))
				{
					$out->{$version} = new StdClass();

					if ($type === 'stable')
					{
						$changelog = $this->get_changelog($version);
						$out->{$version}->date = $changelog->date;
						$out->{$version}->changes = $changelog->changes;
					}
				}

				if (!isset($out->{$version}->zz{$os}))
				{
					$out->{$version}->{$os} = array();
				}

				$out->{$version}->{$os}[] = (object) array(
					'arch' => $arch,
					'ext' => $ext,
					'url' => $item->html_url,
					'size' => $item->size,
					'sha' => $match['sha'],
					'type' => $type
				);
			}

			Cache::set('/axr_releases/:repo/' . $this->repository, $out);
		}

		return $out;
	}

	/**
	 * Get releases by type
	 *
	 * @param string $type (stable|dev)
	 * @return mixed
	 */
	public function get_releases_by_type ($type)
	{
		$out = new StdClass();
		$data = $this->get_releases();

		foreach ($data as $version => $version_data)
		{
			foreach ($version_data as $os => $os_data)
			{
				if (!in_array($os, array('windows', 'osx', 'linux', 'src')))
				{
					continue;
				}

				foreach ($os_data as $i => $release)
				{
					if ($release->type === $type)
					{
						if (!isset($out->{$version}))
						{
							$out->{$version} = new StdClass();
						}

						if (!isset($out->{$version}->{$os}))
						{
							$out->{$version}->{$os} = array();
						}

						$out->{$version}->{$os}[] = clone $release;
					}
				}
			}

			if (isset($out->{$version}))
			{
				$out->{$version}->date = isset($out->{$version}->date) ?
					$out->{$version}->date : null;
				$out->{$version}->changes = isset($out->{$version}->changes) ?
					$out->{$version}->changes : null;
			}
		}

		return $out;
	}

	/**
	 * Get the changelog
	 *
	 * @param string $version
	 * @return mixed
	 */
	public function get_changelog ($version)
	{
		static $lines;

		if (!is_array($lines))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' .
				$this->repository . '/contents/CHANGELOG.md');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

			$response = json_decode(curl_exec($ch));
			curl_close($ch);

			$lines = array();

			if (is_object($response) && isset($response->encoding) &&
				$response->encoding === 'base64')
			{
				$lines = explode("\n", base64_decode($response->content));
			}
		}

		$changes = array();
		$date = null;
		$is_found = false;

		foreach ($lines as $line)
		{
			if (!$is_found)
			{
				preg_match('/^[#]+\s+Version\s+([0-9.]+)/', $line, $match);

				if (is_array($match) && isset($match[1]) &&
					$match[1] === $version)
				{
					$is_found = true;
				}

				continue;
			}

			if (strlen($line) === 0)
			{
				continue;
			}

			if ($line[0] === '#')
			{
				break;
			}

			preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})/', $line, $match);
			if (is_array($match) && count($match) === 2)
			{
				$date = strtotime($match[1]);
			}

			if ($line[0] === '*')
			{
				$changes[] = preg_replace('/\*\s+/', '', $line);
			}
		}

		return (object) array(
			'date' => $date,
			'changes' => $changes
		);
	}

	/**
	 * Get just one latest release for auotmatically detected os and
	 * architecture.
	 *
	 * @return mixed
	 */
	public function get_for_home ()
	{
		$data = (array) $this->get_releases_by_type('stable');

		ksort($data);
		$data = array_reverse($data);

		$clientOS = self::detect_os();
		$clientArch = self::detect_arch();
		$out = null;

		foreach ($data as $version => $item)
		{
			if (!isset($item->{$clientOS}))
			{
				continue;
			}

			for ($i = 0, $c = count($item->{$clientOS}); $i < $c; $i++)
			{
				$release = $item->{$clientOS}[$i];

				if ($release->arch === $clientArch ||
					($release->arch === 'i386' && $clientArch === 'x86_64'))
				{
					$out = $release;
					$out->date = $item->date;
					$out->changes = $item->changes;
					$out->os = $clientOS;
					$out->version = $version;

					if ($release->arch === $clientArch)
					{
						break(2);
					}
				}
			}
		}

		return $out;
	}

	/**
	 * Detect client's OS
	 *
	 * @return string (osx|linux|win)
	 */
	private static function detect_os ()
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
	 * @return string (universal|i386|x86_64)
	 */
	private static function detect_arch ()
	{
		if (self::detect_os() === 'osx')
		{
			return 'universal';
		}

		if (preg_match('/wow64|x86_64|x86-64|x64|amd64/i', $_SERVER['HTTP_USER_AGENT']))
		{
			return 'x86_64';
		}

		return 'i386';
	}
}


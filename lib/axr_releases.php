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

				preg_match('/\.(?<ext>exe|msi|dmg|tar\.gz|deb|rpm)$/', $item->name, $match);

				if (!is_array($match) || count($match) === 0)
				{
					continue;
				}

				$ext = $match['ext'];
				$regex = null;

				switch ($ext)
				{
					case 'exe':
					case 'msi':
						$regex = '/^[a-zA-Z0-9_.-]+-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-windows-(?<arch>x86|x64|ia64)\.(exe|msi)/';
						$os = 'windows';
						break;

					case 'dmg':
						$regex = '/^[a-zA-Z0-9_.-]+-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-osx-(?<arch>i386|x86_64)\.dmg/';
						$os = 'osx';
						break;

					case 'tar.gz':
						$regex = '/^[a-zA-Z0-9_.-]+-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-linux-(?<arch>i386|x86_64)\.tar\.gz/';
						$os = 'linux';
						break;

					case 'rpm':
						$regex = '/^[a-zA-Z0-9_.-]+-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-([^-]+)\.(?<arch>i386|i586|i686|x86_64)\.rpm/';
						$os = 'linux';
						break;

					case 'deb':
						$regex = '/^[a-zA-Z0-9_.-]+_(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)_(?<arch>i386|amd64)\.deb/';
						$os = 'linux';
						break;

					default:
						continue 2;
				}

				// Parse the file name
				preg_match($regex, $item->name, $match);

				if (count($match) === 0)
				{
					continue;
				}

				// Insert some data about the package
				$version = $match['version'];
				$arch = $match['arch'];

				if (!isset($out->{$version}))
				{
					$out->{$version} = new StdClass();

					$changelog = $this->get_changelog($version);
					$out->{$version}->date = $changelog->date;
					$out->{$version}->changes = $changelog->changes;
				}

				if (!isset($out->{$version}->{$os}))
				{
					$out->{$version}->{$os} = array();
				}

				$out->{$version}->{$os}[] = (object) array(
					'arch' => $arch,
					'ext' => $ext,
					'url' => $item->html_url,
					'size' => $item->size,
				);
			}

			Cache::set('/axr_releases/:repo/' . $this->repository, $out);
		}

		return $out;
	}

	/**
	 * Get just one latest release for automatically detected os and
	 * architecture.
	 *
	 * @todo make it better
	 * @return mixed
	 */
	public function get_releases_for_home ()
	{
		$data = (array) $this->get_releases();

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
	 * Get the changelog.
	 * WARNING: There's no caching done here.
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
		$can_continue = false;
		$counter = 0;

		foreach ($lines as $line)
		{
			if ($counter >= 10)
			{
				// We don't want any more items
				break;
			}

			if (!$is_found)
			{
				preg_match('/^[#]+\s+Version\s+([^ ]+)\s+-\s+([0-9]{4}-[0-9]{2}-[0-9]{2}|Unreleased)/', $line, $match);

				if (is_array($match) && isset($match[1]) &&
					$match[1] === $version)
				{
					$is_found = true;
					$date = $match[2];
				}

				continue;
			}

			if (strlen($line) === 0)
			{
				continue;
			}

			if ($line[0] === '#')
			{
				// That's where the next version starts
				break;
			}

			if ($line[0] === '*')
			{
				$changes[] = preg_replace('/^\*\s+/', '', $line);

				$can_continue = true;
				$counter++;

				continue;
			}

			if ($can_continue)
			{
				if (preg_match('/^[ ]{2}(.+)$/', $line, $match))
				{
					$changes[count($changes) - 1] .= $match[1];
				}
				else
				{
					$can_continue = false;
				}
			}
		}

		return (object) array(
			'date' => $date,
			'changes' => $changes
		);
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

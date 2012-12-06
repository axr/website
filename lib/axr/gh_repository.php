<?php

namespace AXR;

class GHRepository
{
	/**
	 * Repository name
	 *
	 * @var string
	 */
	private $repo;

	/**
	 * List of releases
	 *
	 * @var mixed
	 */
	private $releases;

	/**
	 * Constructor
	 *
	 * @param string $repo
	 */
	public function __construct ($repo)
	{
		$this->repo = $repo;
	}

	/**
	 * Load all the data we want about this repositry
	 *
	 * @param bool $ignore_cache
	 */
	public function load ($ignore_cache = false)
	{
		$this->releases = new \StdClass();
		$cache = \Cache::get('/axr/gh_repository/' . $this->repo);

		if ($cache !== null)
		{
			$this->releases = $cache;
			return $cache;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' .
			$this->repo . '/downloads');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

		$files = json_decode(curl_exec($ch));
		curl_close($ch);


		if (!is_array($files))
		{
			$files = array();
		}

		for ($i = 0, $c = count($files); $i < $c; $i++)
		{
			$item = $files[$i];

			preg_match('/\.(?<ext>exe|msi|dmg|tar\.gz|deb|rpm)$/', $item->name, $match);

			if (!is_array($match) || count($match) === 0)
			{
				// Unknown file type
				continue;
			}

			$ext = $match['ext'];
			$regex = null;

			if (preg_match('/^(?<package>[a-zA-Z0-9_.-]+)-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-src\.(tar\.gz|zip)/', $item->name, $match))
			{
				$os = 'src';
			}
			else
			{
				switch ($ext)
				{
					case 'exe':
					case 'msi':
						$regex = '/^(?<package>[a-zA-Z0-9_.-]+)-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-windows-(?<arch>x86|x64|ia64)\.(exe|msi)/';
						$os = 'windows';
						break;

					case 'dmg':
						$regex = '/^(?<package>[a-zA-Z0-9_.-]+)-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-osx-(?<arch>i386|x86_64|universal)\.dmg/';
						$os = 'osx';
						break;

					case 'tar.gz':
						$regex = '/^(?<package>[a-zA-Z0-9_.-]+)-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-linux-(?<arch>i386|x86_64)\.tar\.gz/';
						$os = 'linux';
						break;

					case 'rpm':
						$regex = '/^(?<package>[a-zA-Z0-9_.-]+)-(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)-([^-]+)\.(?<arch>i386|i586|i686|x86_64|noarch)\.rpm/';
						$os = 'linux';
						break;

					case 'deb':
						$regex = '/^(?<package>[a-zA-Z0-9_.-]+)_(?<version>(([0-9.]+){2,4})(\.(alpha|beta|rc)[0-9]+)?)_(?<arch>i386|amd64|all)\.deb/';
						$os = 'linux';
						break;

					default:
						// TODO: Detect source packages here
						continue 2;
				}

				preg_match($regex, $item->name, $match);
			}

			if (!is_array($match) || count($match) === 0)
			{
				continue;
			}

			$package = $match['package'];
			$version = $match['version'];
			$arch = isset($match['arch']) ? $match['arch'] : 'universal';

			if ($os === 'osx' && $arch === 'none')
			{
				$arch = 'osx_uni';
			}

			if (!isset($this->releases->{$version}))
			{
				$this->releases->{$version} = new \StdClass();
				$this->releases->{$version}->version = $version;
				$this->releases->{$version}->packages = new \StdClass();;
			}

			if (!isset($this->releases->{$version}->packages->{$package}))
			{
				$this->releases->{$version}->packages->{$package} = new \StdClass();
				$this->releases->{$version}->packages->{$package}->name = $package;
				$this->releases->{$version}->packages->{$package}->files = array();
			}

			$this->releases->{$version}->packages->{$package}->files[] = (object) array(
				'os' => $os,
				'arch' => self::arch_to_canonical($arch),
				'ext' => $ext,
				'url' => $item->html_url,
				'filename' => $item->name,
				'package' => $package,
				'size' => $item->size,
			);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' .
			$this->repo . '/contents/CHANGELOG.md');
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

		$version = null;

		for ($i = 0, $c = count($lines); $i < $c; $i++)
		{
			$line = $lines[$i];

			if (preg_match('/^[#]+\s+Version\s+(?<version>[^ ]+)\s+-\s+(?<date>[0-9]{4}-[0-9]{2}-[0-9]{2}|Unreleased)/', $line, $match))
			{
				$version = $match['version'];

				if (!isset($this->releases->{$version}))
				{
					$this->releases->{$version} = new \StdClass();
					$this->releases->{$version}->version = $version;
				}

				$this->releases->{$version}->date = strtotime($match['date']);
				$this->releases->{$version}->changelog = array();
			}
			else if ($version !== null)
			{
				if (preg_match('/^[*][ ](?<data>.+)$/', $line, $match))
				{
					$this->releases->{$version}->changelog[] = $match['data'];
				}
				else if (preg_match('/^[ ]{2}(?<data>.+)$/', $line, $match))
				{
					$this->releases->{$version}->changelog[count($this->releases->{$version}->changelog) - 1] .= $match['data'];
				}
			}
		}

		\Cache::set('/axr/gh_repository/' . $this->repo, $this->releases);
	}

	/**
	 * Get all releases
	 *
	 * @return \StdClass[]
	 */
	public function get_releases ()
	{
		return clone $this->releases;
	}

	/**
	 * Get information about a release
	 *
	 * @param string $version
	 * @return \StdClass
	 */
	public function get_release ($version)
	{
		if ($this->releases === null)
		{
			return null;
		}

		if ($version === 'latest')
		{
			$latest = null;

			foreach ($this->releases as $version => $release)
			{
				if (($latest === null || $latest->date < $release->date) &&
					isset($release->packages))
				{
					$latest = $release;
				}
			}

			return is_object($latest) ? clone $latest : null;
		}
		else if (isset($this->releases->{$version}))
		{
			return clone $this->releases->{$version};
		}

		return null;
	}

	/**
	 * Choose one file that is the most suitable for the user
	 *
	 * @param \StdClass[]
	 * @return \StdClass
	 */
	public static function choose_best_file (array $files)
	{
		if (count($files) === 0)
		{
			return null;
		}

		$best = null;
		$distro = self::detect_linux_distro();
		$pref_types = array(
			'debian' => 'deb',
			'ubuntu' => 'deb',
			'centos' => 'rpm',
			'fedora' => 'rpm',
			'redhat' => 'rpm',
			'suse' => 'rpm'
		);

		for ($i = 0, $c = count($files); $i < $c; $i++)
		{
			$file = $files[$i];

			if (self::detect_os() !== $file->os)
			{
				continue;
			}

			if ($file->os === 'osx' && $file->arch === 'osx_uni')
			{
				$best = $file;
				break;
			}

			if ($file->arch !== self::detect_arch())
			{
				continue;
			}

			if ($file->os === 'linux')
			{
				if ($file->ext === 'tar.gz')
				{
					$best = $file;
				}

				if ($distro !== null &&
					array_key_or($pref_types, $distro, null) === $file->ext)
				{
					$best = $file;
					break;
				}
			}
			else
			{
				$best = $file;
			}
		}

		return $best;
	}

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
	 * @return string (universal|x86_64|i386)
	 */
	public static function detect_arch ()
	{
		if (self::detect_os() === 'osx')
		{
			// The UA does not tell us the architecture, so we'll just have to
			// assume it's x86_64
			return 'x86_64';
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
	 * @param string $arch
	 * @return string
	 */
	public static function arch_to_canonical ($arch)
	{
		if (in_array($arch, array('osx_uni', 'ia64')))
		{
			return $arch;
		}

		if (in_array($arch, array('all', 'noarch')))
		{
			return 'none';
		}

		if (in_array($arch, array('x86_64', 'x86-64', 'x64', 'amd64')))
		{
			return 'x86_64';
		}

		return 'i386';
	}
}

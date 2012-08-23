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
	 * Fetch data from GitHub API. This function does no caching
	 *
	 * @return mixed
	 */
	public function fetchData ()
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

			preg_match('/axr_([0-9.]+)_([a-z]+)_([^_\.]+)/', $item->name, $match);

			if (!is_array($match))
			{
				continue;
			}

			$version = $match[1];
			$os = $match[2];
			$arch = $match[3];

			if (!isset($out->{$version}))
			{
				$out->{$version} = new StdClass();
			}

			if (!isset($out->{$version}->{$os}))
			{
				$out->{$version}->{$os} = array();
			}


			$out->{$version}->{$os}[] = (object) array(
				'arch' => $arch,
				'url' => $item->html_url,
				'size' => $item->size
			);
		}

		return $out;
	}

	/**
	 * Get parsed releases data
	 *
	 * @return mixed
	 */
	public function getData ()
	{
		$data = Cache::get('/axr_releases/:repo/' . $this->repository);

		if ($data === null)
		{
		 	$data = $this->fetchData();
			Cache::set('/axr_releases/:repo/' . $this->repository, $data);
		}

		return $data;
	}

	/**
	 * Get the changelog
	 *
	 * @param string $version
	 * @return mixed
	 */
	public function getChangelog ($version)
	{
		$data = Cache::get('/axr_releases/changelog/' . $version);

		if ($data === null)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' .
				$this->repository . '/git/refs/tags');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

			$refs = json_decode(curl_exec($ch));
			curl_close($ch);

			if (!is_array($refs))
			{
				Cache::set('/axr_releases/changelog/' . $version, array());
				return array();
			}

			$sha = null;

			for ($i = 0, $c = count($refs); $i < $c; $i++)
			{
				if ($refs[$i]->ref === "refs/tags/v{$version}-stable")
				{
					$sha = $refs[$i]->object->sha;
				}
			}

			if ($sha === null)
			{
				Cache::set('/axr_releases/changelog/' . $version, array());
				return array();
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' .
				$this->repository . '/git/tags/' . $sha);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

			$tag = json_decode(curl_exec($ch));
			curl_close($ch);

			if (!is_object($tag))
			{
				Cache::set('/axr_releases/changelog/:repo/' .
					$this->repository, array());
				return array();
			}

			$data = array();
			$explode = explode("Changelog:\n", $tag->message);

			if (isset($explode[1]))
			{
				$explode = explode("-----BEGIN PGP SIGNATURE-----\n", $explode[1]);
				$data = explode("\n", $explode[0]);

				$data = array_filter($data, function ($change)
				{
					$change = trim($change);
					return !empty($change);
				});
			}

			Cache::set('/axr_releases/changelog/' . $version, $data);
		}

		return $data;
	}

	/**
	 * Get just one latest release for auotmatically detected os and
	 * architecture.
	 *
	 * @return mixed
	 */
	public function getForHome ()
	{
		$data = (array) $this->getData();

		ksort($data);
		$data = array_reverse($data);

		$clientOS = self::detectOS();
		$clientArch = self::detectArch();
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

				if ($release->arch === $clientArch)
				{
					$out = $release;
					$out->os = $clientOS;
					$out->version = $version;

					break(2);
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
	private static function detectOS ()
	{
		if (preg_match('/Mac/', $_SERVER['HTTP_USER_AGENT']))
		{
			return 'osx';
		}

		if (preg_match('/Linux/', $_SERVER['HTTP_USER_AGENT']))
		{
			return 'linux';
		}

		return 'win';
	}

	/**
	 * Detect client's system architecture
	 *
	 * @return string (universal|i386|x86-64)
	 */
	private static function detectArch ()
	{
		if (self::detectOS() === 'osx')
		{
			return 'universal';
		}

		if (preg_match('/WOW64|x86_64|x64/', $_SERVER['HTTP_USER_AGENT']))
		{
			return 'x86-64';
		}

		return 'i386';
	}
}


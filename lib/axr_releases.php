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

		// TODO Sort by version number

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
	 * Get just one latest release for auotmatically detected os and
	 * architecture.
	 *
	 * @return mixed
	 */
	public function getForHome ()
	{
		$data = $this->getData();

		$versions = get_object_vars($data);
		sort($versions);

		$clientOS = self::detectOS();
		$clientArch = self::detectArch();
		$out = null;

		for ($i = 0, $c = count($versions); $i < $c; $i++)
		{
			$version = $versions[$i];
			$item = $data->{$version};

			if (!isset($item->{$clientOS}))
			{
				continue;
			}

			for ($j = 0, $c2 = count($item->{$clientOS}); $j < $c2; $j++)
			{
				$release = $item->{$clientOS}[$j];

				if ($release->arch === $clientArch)
				{
					$out = $release;
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

		return 'x86';
	}
}


<?php

namespace Hssdoc;

require_once(SHARED . '/lib/axr/pkgtools.php');

class ObjectController extends Controller
{
	public function run ($object_name)
	{
		$view = new \Core\View(ROOT . '/views/object.html');
		$view->cache_condition('data_version', \GitData\GitData::$version);
		$view->cache_condition('object_name', $object_name);

		$view->twig()->addFilter(new \Twig_SimpleFilter('ordinal', function ($n)
		{
			$numbers = array('1st', '2nd', '3rd');
			return isset($numbers[$n - 1]) ? $numbers[$n - 1] : $n . 'th';
		}));

		$view->twig()->addFunction(new \Twig_SimpleFunction('values_table', function ($property)
		{
			static $core_version;

			if (count($property->values) === 0)
			{
				return null;
			}

			if ($core_version === null)
			{
				$core = \GitData\Models\Package::find_by_name('libaxr');
				$core_version = $core->get_latest_version_number();
			}

			$values = $property->values;
			$firsts = array();

			usort($values, function ($a, $b)
			{
				$av = $a->since_version;
				$bv = $b->since_version;
				return ($av < $bv) ? -1 : ($av > $bv ? 1 : 0);
			});

			foreach ($values as $i => &$value)
			{
				if (!isset($firsts[$value->since_version]))
				{
					$value->_count = 0;
					$firsts[$value->since_version] = &$value;
				}

				$value->_ref_url = ObjectController::_ref_to_link($value->value);

				if (isset($value->link_to))
				{
					// The previously generated link is intentionally overwritten so
					// that the automatic generation of links from the value can be
					// disabled when needed.
					$value->_ref_url = ObjectController::_ref_to_link($value->link_to);
				}

				// This is a future version
				if (\AXR\Pkgtools::compare_versions($value->since_version, $core_version) === 1)
				{
					$value->_is_future = true;
				}

				$firsts[$value->since_version]->_count++;
			}

			$view = new \Core\View(ROOT . '/views/values_table.html');

			$view->values = $values;
			$view->property = $property;

			return $view->get_rendered();
		}));

		if (!$view->load_from_cache())
		{
			$object = \GitData\Models\HssdocObject::find_by_name($object_name);

			if ($object === null)
			{
				throw new \HTTPException(null, 404);
			}

			$view->object = $object;
			$view->properties = $object->get_properties();
			$view->sidebar = (string) new SidebarView();

			$parent = $object;
			while ($parent = $parent->get_owner())
			{
				$this->breadcrumb->push($parent->name, $parent->permalink);
			}

			$this->breadcrumb->push($object_name, $object->permalink);
		}

		$this->layout->title = $object_name;
		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}

	/**
	 * Convert a HSS doc. reference to a link.
	 *
	 * Supported formats:
	 * - @OBJECT
	 * - @OBJECT<PROPERTY>
	 * - @OBJECT<PROPERTY>[VALUE]
	 *
	 * @param string $ref
	 * @return \URL
	 */
	public static function _ref_to_link ($ref)
	{
		$link = null;

		if (preg_match('/^(?<object>@[a-zA-Z]+)(<(?<property>[a-zA-Z]+)>(\[(?<value>.+?)\])?)?$/', $ref, $match))
		{
			$link = \Router::get_instance()->url
				->copy()
				->path('/' . $match['object']);

			if (isset($match['property']))
			{
				$fragment = $match['property'];

				if (isset($match['value']))
				{
					$fragment .= '[' . $match['value'] . ']';
				}

				$link->fragment($fragment);
			}
		}

		return $link;
	}
}

<?php

namespace Hssdoc;

require_once(SHARED . '/lib/axr/pkgtools.php');

class ObjectController extends Controller
{
	public function run ($object_name)
	{
		$html = $this->get_cached_page('/hssdoc/@' . $object_name);

		if ($html !== null)
		{
			echo $html;
			return;
		}

		$object = \GitData\Models\HssdocObject::find_by_name($object_name);

		if ($object === null)
		{
			throw new \HTTPException(null, 404);
		}

		if (is_array($object->shorthand_stack) &&
			count($object->shorthand_stack) > 0)
		{
			$numbers = array('1st', '2nd', '3rd');
			$object->_shorthand_stack = array();

			foreach ($object->shorthand_stack as $i => $property_name)
			{
				$object->_shorthand_stack[] = array(
					'number' => isset($numbers[$i]) ? $numbers[$i] : $i . 'th',
					'property' => $object->get_property_by_name($property_name)
				);
			}
		}

		$properties = $object->get_properties();

		foreach ($properties as &$property)
		{
			$property->_values_table = $this->render_values_table($property);

			if (count($property->text_scope) > 0)
			{
				$property->_text_scope = array();

				foreach (array('line', 'word', 'character') as $name)
				{
					$property->_text_scope[] = array(
						'name' => $name,
						'is_valid' => in_array($name, $property->text_scope)
					);
				}
			}
		}

		$this->view->_title = $object->name;
		$this->breadcrumb[] = array(
			'name' => $object->name
		);

		$this->view->object = $object;
		$this->view->properties = $properties;
		$this->view->sidebar = Sidebar::render();

		echo $this->render_page(ROOT . '/views/object.html', array(
			'cache_key' => '/hssdoc/@' . $object_name
		));
	}

	/**
	 * Render HSS documentation values table
	 *
	 * @param \GitData\Models\HssdocProperty
	 * @return string
	 */
	private function render_values_table (\GitData\Models\HssdocProperty $property)
	{
		if (count($property->values) === 0)
		{
			return null;
		}

		$cache_key = '/hssdoc/values_table_html/' . $property->object_name .
			'/' . $property->name;
		$html = \Cache::get($cache_key);

		if ($html !== null)
		{
			return $html;
		}

		// This version obtaining code is not cached so it is important that the
		// rendered values table is cached.
		$core = \GitData\Models\Package::find_by_name('libaxr');
		$core_version = $core->get_latest_version_number();

		$values = $property->values;
		$first_of_ver = array();

		foreach ($values as $i => &$value)
		{
			if (!isset($first_of_ver[$value->since_version]))
			{
				$value->_count = 0;
				$first_of_ver[$value->since_version] = &$value;
			}

			$value->_ref_url = $this->ref_to_link($value->value);

			if (isset($value->link_to))
			{
				// The previously generated link is intentionally overwritten so
				// that the automatic generation of links from the value can be
				// disabled when needed.
				$value->_ref_url = $this->ref_to_link($value->link_to);
			}


			// This is a future version
			if (\AXR\Pkgtools::compare_versions($value->since_version, $core_version) === 1)
			{
				$value->_is_future = true;
			}

			$first_of_ver[$value->since_version]->_count++;
		}

		$view = new \StdClass();
		$view->values = $values;
		$view->property = $property;

		$mustache = new \Mustache\Renderer();
		$template = file_get_contents(ROOT . '/views/values_table.html');

		// Render the values table
		$html = $mustache->render($template, $view);

		// And cache it
		\Cache::set($cache_key, $html, array(
			'data_version' => 'current'
		));

		return $html;
	}

	private function ref_to_link ($ref)
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

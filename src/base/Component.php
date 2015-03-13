<?php
/**
 * @link http://buildwithcraft.com/
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc.
 * @license http://buildwithcraft.com/license
 */

namespace craft\app\base;

use yii\base\Model;
use yii\base\Object;

/**
 * Component is the base class for classes representing Craft components in terms of objects.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
abstract class Component extends Model implements ComponentInterface
{
	// Public Methods
	// =========================================================================

	/**
	 * Returns the display name of this class.
	 * @return string The display name of this class.
	 */
	public static function classDisplayName()
	{
		$classNameParts = implode('\\', static::className());
		$displayName = array_pop($classNameParts);
		return $displayName;
	}

	/**
	 * @inheritdoc
	 */
	public static function classHandle()
	{
		$classNameParts = implode('\\', static::className());
		$handle = array_pop($classNameParts);
		return strtolower($handle);
	}

	/**
	 * @inheritdoc
	 */
	public static function instantiate($data)
	{
		if ($data['type'])
		{
			$class = $data['type'];
			return new $class;
		}
		else
		{
			return new static;
		}
	}

	/**
	 * @inheritdoc
	 */
	public static function populateComponent(ComponentInterface $component, $data)
	{
		if ($component instanceof Model)
		{
			$attributes = array_flip($component->attributes());
		}

		foreach ($data as $name => $value)
		{
			if (isset($attributes[$name]) || ($component instanceof Object && $component->canSetProperty($name)) || property_exists($component, $name))
			{
				$component->$name = $value;
			}
		}
	}
}

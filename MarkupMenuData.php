<?php

namespace ProcessWire;

/**
 * MarkupMenu menu item data
 *
 * This is a wrapper class for WireData, providing some additional features.
 * 
 * @version 0.2.0
 * @author Teppo Koivula <teppo.koivula@gmail.com>
 * @license Mozilla Public License v2.0 http://mozilla.org/MPL/2.0/
 */
class MarkupMenuData extends WireData {

    /**
     * Constructor method
     *
     * @param array Stored values
     */
    public function __construct(array $values = []) {
        if (isset($values['classes']) && is_array($values['classes'])) {
            $values['classes'] = implode(' ', $values['classes']);
        }
        $this->setArray($values);
    }

	/**
	 * Retrieve the value for a previously set property
	 *
 	 * @param string|object $key Name of property you want to retrieve. 
	 * @return mixed|null Returns value of requested property, or null if the property was not found. 
	 * @see WireData::set()
	 */
	public function get($key) {
		if(strpos($key, '.')) return $this->getDot($key);
		return parent::get($key); // back to WireData
	}

}

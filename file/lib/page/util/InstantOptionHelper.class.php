<?php
namespace wcf\page\util;
use \wcf\util\ClassUtil;
use \wcf\system\WCF;

/**
 * @author		kaffeemon
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.github.kaffeemon.wcf.instantOptions
 * @subpackage	page.util
 */
class InstantOptionHelper {
	public static $typeObjs = array();
	
	public $name = '';
	public $langPrefix = '';
	
	public $enableAssignVariables = true;
	public $options = array();
	public $values = array();
	public $errors = array();
	
	public function __construct($name, $langPrefix) {
		$this->name = $name;
		$this->langPrefix = $langPrefix;
	}
	
	/**
	 * Registers options.
	 */
	public function registerOptions(array $options) {
		$this->options = array_merge($this->options, $options);
		
		foreach ($options as $option) {
			if ($option->defaultValue)
				$this->values[$option->optionName] = $option->defaultValue;
		}
	}
	
	/**
	 * Reads new values from request data.
	 */
	public function readValues() {
		foreach ($this->options as $option) {
			$value = null;
			if (isset($_POST[$this->name]) && is_array($_POST[$this->name]) && isset($_POST[$this->name][$option->optionName]))
				$value = $_POST[$this->name][$option->optionName];
			
			$this->values[$option->optionName] = static::getTypeObject($option->optionType)->getData($option, $value);
		}
	}
	
	/**
	 * Validates user input.
	 */
	public function validate($callback = null) {
		foreach ($this->options as $option) {
			try {
				if ($option->validationPattern) {
					if (!preg_match('~'.$option->validationPattern.'~', $this->values[$option->optionName]))
						throw new \wcf\system\exception\UserInputException($option->optionName, 'validationFailed');
				}
				
				static::getTypeObject($option->optionType)->validate($option, $this->values[$option->optionName]);
			} catch (\wcf\system\exception\UserInputException $e) {
				$this->errors[$e->getField()] = $e->getType();
			}
		}
		
		if (is_callable($callback)) {
			try {
				$callback($this->options);
			catch (\wcf\system\exception\UserInputException $e) {
				$this->errors = array_merge($this->errors, $e->getType());
			}
		}
		
		if (count($this->errors))
			throw new \wcf\system\exception\UserInputException($this->name, $this->errors);
	}
	
	/**
	 * Sets initial values.
	 */
	public function setValues(array $values) {
		$this->values = $values;
	}
	
	/**
	 * Returns the values of the options as an array.
	 */
	public function getValues() {
		return $this->values;
	}
	
	/**
	 * Returns the template.
	 */
	public function render() {
		$options = array();
		$errors = array();
		
		if ($this->enableAssignVariables) {
			foreach ($this->options as $option) {
				$optionValue = null;
				if (isset($this->values[$option->optionName]))
					$optionValue = $this->values[$option->optionName];
			
				$options[] = array(
					'object' => $option,
					'html' => static::getTypeObject($option->optionType)->getFormElement($option, $optionValue),
					'cssClassName' => static::getTypeObject($option->optionType)->getCSSClassName(),
					'error' => (isset($this->errors[$option->optionName]) ? $this->errors[$option->optionName] : '')
				);
			}
		}
		
		return WCF::getTPL()->fetch('instantOptions', array(
			'name' => $this->name,
			'langPrefix' => $this->langPrefix,
			'options' => $options
		));
	}
	
	/**
	 * Disables assignment of variables in assignVariables().
	 */
	public function disableAssignVariables() {
		$this->enableAssignVariables = false;
	}
	
	/**
	 * Returns an object of the requestes option type.
	 */
	public static function getTypeObject($type) {
		if (!isset(static::$typeObjs[$type])) {
			if (class_exists($className = 'wcf\system\option\\'.ucfirst($type).'OptionType') {
				&& ClassUtil::isInstanceOf($className, 'wcf\system\option\IOptionType'))
				
				static::$typeObjs[$type] = new $className;
			} else if (class_exists($className = 'wcf\system\option\user\group\\'.ucfirst($type).'UserGroupOptionType') {
				&& ClassUtil::isInstanceOf($className, 'wcf\system\option\IOptionType'))
				
				static::$typeObjs[$type] = new $className;
			} else
				static::$typeObjs[$type] = null;
		}
		
		return static::$typeObjs[$type];
	}
}


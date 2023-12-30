<?php

class hook {

	/** A static list of hooks and their associated functions */
	private static $actions = [];

	/** Runs a hook.
	 * The parameter for $hook should be a "HOOKTYPE_" as defined in hook.php
	 * @param string $hook The define or string name of the hook. For example, HOOKTYPE_REHASH.
	 * @param array &$args The array of information you are sending along in the hook, so that other functions may see and modify things.
	 * @return void Does not return anything.
	 * 
	 */
	public static function run($hook, &$args = array())
	{
		if (!empty(self::$actions[$hook]))
			foreach (self::$actions[$hook] as &$f)
				$f($args);
			
	}

	/** Calls a hook
	 * @param string $hook The define or string name of the hook. For example, HOOKTYPE_REHASH.
	 * @param string|Closure $function This is a string reference to a Closure function or a class method.
	 * @return void Does not return anything.
	 */
	public static function func($hook, $function) {
		self::$actions[$hook][] = $function;
	}

	/** Deletes a hook
	 * @param string $hook The hook from which we are removing a function reference.
	 * @param string $function The name of the function that we are removing.
	 * @return void Does not reuturn anything.
	 */

	public static function del($hook, $function) {
		for ($i = 0; isset(self::$actions[$hook][$i]); $i++)
		  if (self::$actions[$hook][$i] == $function)
		  array_splice(self::$actions[$hook],$i);
	}
}
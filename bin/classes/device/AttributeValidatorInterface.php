<?php namespace attribute;

/**
 * This interface provides access to the system's validation subsystem. The classes
 * implementing this interface will be able to indicate the kind of data they can 
 * validate and validate said data.
 * 
 * Classes will not define how the data is read and written, this is on the FW 
 * logic, since PHPAuthserver only accepts a few datatypes by default.
 * 
 * @todo In future releases it'd be interesting to let the validator provide a 
 * object that will define the settings for these validators and the values a 
 * user can provide.
 */
interface AttributeValidatorInterface extends \spitfire\validation\ValidationRule
{
	
	/**
	 * The constructor for classes that implement this interface needs to be 
	 * standard, so the system can create objects without any issue.
	 */
	function __construct();
	
	/**
	 * This method allows the system to provide the validator with the appropriate
	 * settings after it was instanced.
	 * 
	 * @param string[] $settings
	 */
	function load($settings);
	
	/**
	 * Can return one of the strings that the system uses to address attribute
	 * types. These range from string to file.
	 */
	function validates();
	
	/**
	 * Returns the name for this validator. This should assist the user finding
	 * the validator he needs
	 */
	function getName();
	
	/**
	 * The error message that will be displayed to the user in the event of them
	 * providing a value that is not acceptable.
	 */
	function getErrorMsg();
	
	/**
	 * This will be shown to the user entering the data before entering the data,
	 * this ensures that the user is informed beforehand of the restrictions.
	 * 
	 * @return string
	 */
	function getDescription();
	
}

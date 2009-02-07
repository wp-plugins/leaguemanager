<?php
/**
* Image class for the WordPress plugin LeagueManager
* Main functions are performed by Thumbnail class by Ian Selby
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2008-2009
*/

class LeagueManagerImage extends LeagueManager
{
	/**
	* supported image types
	*
	* @var array
	*/
	var $supported_image_types = array( "jpg", "jpeg", "png", "gif" );
	
	
	/**
	 * image filename
	 *
	 * @var string
	 */
	var $image;
	
	
	/**
	 * thumbnail class object
	 *
	 * @var object
	 */
	var $thumbnail;
	
	
	/**
	* Initializes plugin
	*
	* @param none
	* @return void
	*/
	function __construct($imagefile = false)
	{
		if ( !class_exists("Thumbnail") )
			require_once( dirname (__FILE__) . '/thumbnail.inc.php' );
			
		$this->image = $imagefile;
		if ( $imagefile )
			$this->thumbnail = new Thumbnail($imagefile);
	}
	function LeagueManagerImage($imagefile)
	{
		$this->__construct($imagefile);
	}
	
	
	/**
	 * gets supported file types
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return $this->supported_image_types;
	}
	
	
	/**
	 * checks if image type is supported
	 *
	 * @param string $filename image file
	 * @return boolean
	 */
	function supported()
	{
		if ( in_array($this->getImageType(), $this->getSupportedImageTypes()) )
			return true;
		else
			return false;
	}
	
	
	/**
	 * gets image type of supplied image
	 *
	 * @param none
	 * @return file extension
	 */
	function getImageType(  )
	{
		$file_info = pathinfo($this->image);
		return strtolower($file_info['extension']);
	}
	
	
	/**
	 * create Thumbnail
	 *
	 * @param none
	 */
	function createThumbnail()
	{
		$this->thumbnail->resize( 30, 30 );
		$this->thumbnail->save($this->image);
	}
}

?>
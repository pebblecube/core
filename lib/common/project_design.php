<?php
/**
 * project class
 *
 **/
class project_design
{
	/**
	 * content background color
	 *
	 * @var string
	 */
	var $body_color;
	
	/**
	 * text color of titles
	 *
	 * @var string
	 */
	var $primary_text_color;
	
	/**
	 * content text color
	 *
	 * @var string
	 */
	var $seconday_text_color;
	
	/**
	 * hyperlinks color
	 *
	 * @var string
	 */
	var $link_color;
	
	/**
	 * page background color
	 *
	 * @var string
	 */
	var $background_color;
	
	/**
	 * header image
	 *
	 * @var string
	 */
	var $header_image;
	
	/**
	 * constructor
	 *
	 * @param array $array
	 */
	function __construct($array = NULL) 
	{
		//set project values
		if(is_array($array))
		{
			$this->body_color = $array['body_color'];
			$this->primary_text_color = $array['primary_text_color'];
			$this->seconday_text_color = $array['seconday_text_color'];
			$this->link_color = $array['link_color'];
			$this->background_color = $array['background_color'];
			$this->header_image = $array['header_image'];
		}
	}

	/**
	 * returns JSON of the object
	 *
	 * @return string
	 **/
	function toJson()
	{
		return json_encode($this->toArray());
	}
	
	/**
	 * returns object array representation
	 *
	 * @return Array
	 **/
	function toArray()
	{
		return array(
		  	'body_color' => $this->body_color,
		  	'primary_text_color' => $this->primary_text_color,
		  	'seconday_text_color' => $this->seconday_text_color,
		  	'link_color' => $this->link_color,
		  	'background_color' => $this->background_color,
		  	'header_image' => $this->header_image
		);
	}
	
} // END class 
?>
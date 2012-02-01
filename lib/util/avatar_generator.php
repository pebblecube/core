<?php 
class avatar_generator 
{
	public static function generate_random($user_id, $num, $sizes)
	{
		$matrix = avatar_generator::create_random_matrix($num);
			
		for($s = 0; $s < sizeof($sizes); $s++)
		{
			$size = $sizes[$s];
			$invader = imagecreatetruecolor($size, $size);
			
			//fill black background
			$bg = imagecolorallocate($invader, 255, 255, 255);
			imagefilledrectangle($invader, 0, 0, $size, $size, $bg);
			
			$square_size = $size/$num; //size of each pixel of the invader
			//var_dump($matrix);
			//cycle the matrix... true white, false black
			for ($i = 0; $i < sizeof($matrix); $i++)
			{
				for ($j = 0; $j < sizeof($matrix[$i]); $j++)
				{	
					//echo($square_size*$i."-".$square_size*$j."-".$square_size*($i+1)."-".$square_size*($j+1)."\n");
					if($matrix[$i][$j])
					{	
						$square_bg = imagecolorallocate($invader, 0, 0, 0);
						imagefilledrectangle($invader, $square_size*$i, $square_size*$j, $square_size*($i+1), $square_size*($j+1), $square_bg);
					}
				}
			}
			// draw reflected
			$i = sizeof($matrix) - 2;
			$cx = sizeof($matrix) * $square_size;
			while( $i >= 0 ) 
			{
				for ($j = 0; $j < sizeof($matrix[$i]); $j++) 
				{
					//echo($cx*$i."-".$square_size*$j."-".($cx+$square_size)."-".$square_size*($j+1)."\n");
					if($matrix[$i][$j])
					{	
						$square_bg = imagecolorallocate($invader, 0, 0, 0);
						imagefilledrectangle($invader, $cx, $square_size*$j, ($cx+$square_size), $square_size*($j+1), $square_bg);
					}
				}
				$cx += $square_size;
				//echo $cx."\n";
				$i--;
			}
		
			// Save the image
			imagepng($invader, GLOBAL_WWW_FILE_PATH.GLOBAL_AVATAR_FOLDER.DIRECTORY_SEPARATOR."$user_id-$size.png");
			imagedestroy($invader);
		}
	}

	/**
	 * generates a random matrix for the alien avatar
	 **/
	public static function create_random_matrix($num)
	{
		$invader = array();
		$columns = (($num - 1) / 2 + 1);
		for ($i = 0; $i < $columns; $i++)
		{
			$column = array();
			
			for($j = 0; $j < $num; $j++)
				array_push($column, avatar_generator::get_random_bool());
				
			array_push($invader, $column);
		}
		return $invader;
	}

	/**
	 * generates a random boolean value
	 **/
	public static function get_random_bool()
	{
		return round(mt_rand(0.000001, 1)) == 0 ? TRUE : FALSE;
	}

} // END class
?>
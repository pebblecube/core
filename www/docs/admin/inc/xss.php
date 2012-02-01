<?php
	function antixss($str){
		// attributes blacklist:
		$attr = array('style','on[a-z]+');
		// elements blacklist:
		$elem = array('script','iframe','embed','object');
		// extermination:
		$str = preg_replace('#<!--.*?-->?#', '', $str);
		$str = preg_replace('#<!--#', '', $str);
		$str = preg_replace('#(<[a-z]+(\s+[a-z][a-z\-]+\s*=\s*(\'[^\']*\'|"[^"]*"|[^\'">][^\s>]*))*)\s+href\s*=\s*(\'javascript:[^\']*\'|"javascript:[^"]*"|javascript:[^\s>]*)((\s+[a-z][a-z\-]*\s*=\s*(\'[^\']*\'|"[^"]*"|[^\'">][^\s>]*))*\s*>)#is', '$1$5', $str);
		foreach($attr as $a) {
		    $regex = '(<[a-z]+(\s+[a-z][a-z\-]+\s*=\s*(\'[^\']*\'|"[^"]*"|[^\'">][^\s>]*))*)\s+'.$a.'\s*=\s*(\'[^\']*\'|"[^"]*"|[^\'">][^\s>]*)((\s+[a-z][a-z\-]*\s*=\s*(\'[^\']*\'|"[^"]*"|[^\'">][^\s>]*))*\s*>)';
		    $str = preg_replace('#'.$regex.'#is', '$1$5', $str);
		}
		foreach($elem as $e) {
			$regex = '<'.$e.'(\s+[a-z][a-z\-]*\s*=\s*(\'[^\']*\'|"[^"]*"|[^\'">][^\s>]*))*\s*>.*?<\/'.$e.'\s*>';
		    $str = preg_replace('#'.$regex.'#is', '', $str);
		}
		return $str;
	}
?>
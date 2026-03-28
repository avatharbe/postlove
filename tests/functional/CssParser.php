<?php
/**
 * Post Love extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 Stanislav Atanasov
 * @copyright (c) 2026 Avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\postlove\tests\functional;

/**
* CSS parsing utility for test assertions.
*
* This is NOT a test class itself. It provides helper methods to load CSS
* from strings or files, parse it into a structured PHP array (keyed by
* @media block and selector), and optionally reconstitute it via glue().
*
* Used by other functional tests to verify that the extension's CSS files
* are loaded correctly and contain expected selectors/properties.
*
* The parsed output structure is:
*   ['main' => ['selector' => ['property' => 'value', ...], ...],
*    '@media ...' => ['selector' => [...], ...]]
*/
class CssParser {

	/** @var string Raw CSS text loaded via load_string/load_file */
	public $css;

	/** @var array Parsed CSS structure, populated by parse() */
	public $parsed;


	/**
	 * Load a CSS string into the parser.
	 *
	 * @param string $string    The CSS text to load
	 * @param bool   $overwrite If true, replaces existing CSS; if false, appends
	 */
	public function load_string($string, $overwrite = false){
		if($overwrite){
			$this->css = $string;
		} else {
			$this->css .= $string;
		}
	}


	/**
	 * Load CSS from a file path.
	 *
	 * @param string $file      Path to the CSS file
	 * @param bool   $overwrite If true, replaces existing CSS; if false, appends
	 */
	public function load_file($file, $overwrite = false){
		$this->load_string(file_get_contents($file), $overwrite);
	}


	/**
	 * Load multiple CSS files at once.
	 *
	 * @param string $files Semicolon-separated list of file paths
	 */
	public function load_files($files){
		$files = explode(';', $files);
		foreach($files as $file){
			$this->load_file($file, false);
		}
	}


	/**
	 * Parse the loaded CSS text into a structured array.
	 *
	 * Processing steps:
	 * 1. Strip CSS and HTML comments
	 * 2. Extract @media blocks and the remaining "main" rules
	 * 3. Split each block into selector => property => value mappings
	 * 4. Handle !important precedence (later !important overrides earlier)
	 *
	 * Result is stored in $this->parsed.
	 */
	public function parse(){
		$css = $this->css;
		// Remove CSS-Comments
		$css = preg_replace('/\/\*.*?\*\//ms', '', $css);
		// Remove HTML-Comments
		$css = preg_replace('/([^\'"]+?)(\<!--|--\>)([^\'"]+?)/ms', '$1$3', $css);
		// Extract @media-blocks into $blocks
		preg_match_all('/@.+?\}[^\}]*?\}/ms',$css, $blocks);
		// Append the rest to $blocks
		array_push($blocks[0],preg_replace('/@.+?\}[^\}]*?\}/ms','',$css));
		$ordered = array();
		for($i=0;$i<count($blocks[0]);$i++){
			// If @media-block, strip declaration and parenthesis
			if(substr($blocks[0][$i],0,6) === '@media') 
			{
				$ordered_key = preg_replace('/^(@media[^\{]+)\{.*\}$/ms','$1',$blocks[0][$i]);
				$ordered_value = preg_replace('/^@media[^\{]+\{(.*)\}$/ms','$1',$blocks[0][$i]);
			}
			// Rule-blocks of the sort @import or @font-face
			elseif(substr($blocks[0][$i],0,1) === '@')
			{
				$ordered_key = $blocks[0][$i];
				$ordered_value = $blocks[0][$i];
			}
			else 
			{
				$ordered_key = 'main';
				$ordered_value = $blocks[0][$i];
			}
			// Split by parenthesis, ignoring those inside content-quotes
			$ordered[$ordered_key] = preg_split('/([^\'"\{\}]*?[\'"].*?(?<!\\\)[\'"][^\'"\{\}]*?)[\{\}]|([^\'"\{\}]*?)[\{\}]/',trim($ordered_value," \r\n\t"),-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
		}
		
		// Beginning to rebuild new slim CSS-Array
		foreach($ordered as $key => $val){
			$new = array();
			for($i = 0; $i<count($val); $i++){
				// Split selectors and rules and split properties and values
				$selector = trim($val[$i]," \r\n\t");
				
				if(!empty($selector)){
					if(!isset($new[$selector])) $new[$selector] = array();
					$rules = explode(';',$val[++$i]);
					foreach($rules as $rule){
						$rule = trim($rule," \r\n\t");
						if(!empty($rule)){
							$rule = array_reverse(explode(':', $rule));
							$property = trim(array_pop($rule)," \r\n\t");
							$value = implode(':', array_reverse($rule));
							
							if(!isset($new[$selector][$property]) || !preg_match('/!important/',$new[$selector][$property])) $new[$selector][$property] = $value;
							elseif(preg_match('/!important/',$new[$selector][$property]) && preg_match('/!important/',$value)) $new[$selector][$property] = $value;
						}
					}
				}
			}
			$ordered[$key] = $new;
		}
		$this->parsed = $ordered;
	}


	/**
	 * Reconstitute the parsed CSS array back into a CSS string.
	 *
	 * Iterates over $this->parsed and rebuilds valid CSS text with
	 * proper indentation for @media blocks. Returns the CSS string
	 * or null if nothing has been parsed.
	 *
	 * @return string|null The reconstructed CSS text
	 */
	public function glue(){
		if($this->parsed){
			$output = '';
			foreach($this->parsed as $media => $content){
				if(substr($media,0,6) === '@media'){
					$output .= $media . " {\n";
					$prefix = "\t";
				}
				else $prefix = "";
				
				foreach($content as $selector => $rules){
					$output .= $prefix.$selector . " {\n";
					foreach($rules as $property => $value){
						$output .= $prefix."\t".$property.': '.$value;
						$output .= ";\n";
					}
					$output .= $prefix."}\n\n";
				}
				if(substr($media,0,6) === '@media'){
					$output .= "}\n\n";
				}
			}
			return $output;
		}
	}


}


?>
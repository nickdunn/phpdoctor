<?php
/*
PHPDoctor: The PHP Documentation Creator
Copyright (C) 2004 Paul James <paul@peej.co.uk>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/** This generates the HTML API documentation for each global function.
 *
 * @package PHPDoctor\Doclets\Standard
 */
class DelegateWriter extends HTMLWriter
{
		
	function getFiles($start_dir='.') {

	  $files = array();
	  if (is_dir($start_dir)) {
	    $fh = opendir($start_dir);
	    while (($file = readdir($fh)) !== false) {
	      # loop through the files, skipping . and .., and recursing if necessary
	      if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
	      $filepath = $start_dir . '/' . $file;
	      if ( is_dir($filepath) )
	        $files = array_merge($files, $this->getFiles($filepath));
	      else
	        array_push($files, $filepath);
	    }
	    closedir($fh);
	  } else {
	    # false if the function was called with an invalid non-directory argument
	    $files = false;
	  }

	  return $files;

	}

	/** Build the function definitons.
	 *
	 * @param Doclet doclet
	 */
	function delegateWriter(&$doclet)
    {
	
		parent::HTMLWriter($doclet);
		
		$this->_id = 'definition';

		$rootDoc =& $this->_doclet->rootDoc();
		$phpdoctor =& $this->_doclet->phpdoctor();
		
		$path = $phpdoctor->_options['source_path'];
		$files = $this->getFiles($path);
		
		$all_delegates = array();
		
		ob_start();
		
		foreach($files as $file) {
			
			$source = file_get_contents($file);
			
			$delegates = array();
			
			$in_parsed_string = FALSE;
			$counter = 0;
            $lineNumber = 1;
            $commentNumber = 0;
            
			$tokens = token_get_all($source);			
			$numOfTokens = count($tokens);
			
            for ($key = 0; $key < $numOfTokens; $key++) {
                $token = $tokens[$key];
                
                if (!$in_parsed_string && is_array($token)) {
                    
                    $lineNumber += substr_count($token[1], "\n");
                    
                    switch ($token[0]) {
                    
	                    case T_COMMENT: // read comment
	                    case T_ML_COMMENT: // and multiline comment (deprecated in newer versions)
	                    case T_DOC_COMMENT: // and catch PHP5 doc comment token too
							$comment = $token[1];
							if (preg_match("/@delegate/", $comment)) {
								
								$delegate = (object)array();
								$delegate->params = array();
								
								$tags = $this->processDocComment($comment);
								
								foreach($tags as $tag) {
									switch($tag['type']) {
										case 'text':
										$delegate->description = $tag['text'];
										break;
										case '@delegate':
										$delegate->name = $tag['text'];
										break;
										case '@param':
										$delegate->params[] = $tag['text'];
										break;
									}
								}
								
								$delegates[] = $delegate;
								
							}
	                        break;
					}
				}
			}
			
			foreach($delegates as $delegate) {
				
				$all_delagates = $delegate;
				//echo $delegate;
				//echo '<br>';
				
			}
		}
		
		$this->_output = ob_get_contents();
		
		ob_end_clean();
		
    	$this->_write('delegates.html', 'Delegates', TRUE);

	
	}
	
	// modified from phpDoctor class
	function processDocComment($comment)
    {
		if (substr(trim($comment), 0, 3) != '/**') return array(); // not doc comment, abort
        
		$tags = array();
		
		$explodedComment = preg_split('/[\n|\r][ \r\n\t\/]*\*[ \t]*@/', "\n".$comment);
		$matches = array();
		preg_match_all('/^[ \t\/*]*\** ?(.*)[ \t\/*]*$/m', array_shift($explodedComment), $matches);
		if (isset($matches[1])) {
			$tags[] = array(
				'type' => 'text',
				'text' => trim(implode("\n", $matches[1]), " \n\r\t\0\x0B*/"),
				//'data' => $data
			);
			//$data['tags']['@text'] = $this->createTag('@text', trim(implode("\n", $matches[1]), " \n\r\t\0\x0B*/"), $data, $root);
		}
		
		foreach ($explodedComment as $tag) { // process tags
            // strip whitespace, newlines and asterisks
            $tag = preg_replace('/(^[\s\n\r\*]+|\s*\*\/$)/m', ' ', $tag);
            $tag = preg_replace('/[\r\n]+/', '', $tag);
            $tag = trim($tag);
			
			$parts = preg_split('/\s+/', $tag);
			$name = isset($parts[0]) ? array_shift($parts) : $tag;
			$text = join(' ', $parts);
			if ($name) {
				switch ($name) {
				default: //create tag
					$name = '@'.$name;
					if (isset($data['tags'][$name])) {
						if (is_array($data['tags'][$name])) {
							//$data['tags'][$name][] = $this->createTag($name, $text, $data, $root);
						} else {
							//$data['tags'][$name] = array($data['tags'][$name], $this->createTag($name, $text, $data, $root));
						}
					} else {
						//$data['tags'][$name] =& $this->createTag($name, $text, $data, $root);
					}
					$tags[] = array(
						'type' => $name,
						'text' => $text,
						//'data' => $data
					);
				}
			}
		}
		return $tags;
	}

}

?>

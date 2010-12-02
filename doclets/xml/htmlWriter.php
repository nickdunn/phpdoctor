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

/** This generates the index.html file used for presenting the frame-formated
 * "cover page" of the API documentation.
 *
 * @package PHPDoctor\Doclets\Standard
 */
class HTMLWriter
{

	/** The doclet that created this object.
	 *
	 * @var doclet
	 */
	var $_doclet;

	/** The section titles to place in the header and footer.
	 *
	 * @var str[][]
	 */
	var $_sections = NULL;

	/** The directory structure depth. Used to calculate relative paths.
	 *
	 * @var int
	 */
	var $_depth = 0;

	/** The <body> id attribute value, used for selecting style.
	 *
	 * @var str
	 */
	var $_id = 'overview';

	/** The output body.
	 *
	 * @var str
	 */
	var $_output = '';

	/** Writer constructor.
	 */
	function htmlWriter(&$doclet)
    {	
		$this->_doclet =& $doclet;
	}

	/** Build the HTML header. Includes doctype definition, <html> and <head>
	 * sections, meta data and window title.
	 *
	 * @return str
	 */
	function _htmlHeader($title)
    {
		return '';
	}
    
    /** Get the HTML DOCTYPE for this output
     *
     * @return str
     */
    function _doctype()
    {
        return '';
    }
	
	/** Build the HTML footer.
   *
   * @return str
   */
	function _htmlFooter()
    {
		return '';
	}

	/** Build the HTML shell header. Includes beginning of the <body> section,
	 * and the page header.
	 *
	 * @return str
	 */
	function _shellHeader($path)
    {	
		return '';
	}
	
	/** Build the HTML shell footer. Includes the end of the <body> section, and
	 * page footer.
	 *
	 * @return str
	 */
	function _shellFooter($path)
    {
		return '';
	}
	
	/** Build the navigation bar
	 *
	 * @return str
	 */
	function _nav($path)
    {		
		return '';
	}
	
	function _sourceLocation($doc)
	{
	    if ($this->_doclet->includeSource()) {
	        $url = strtolower(str_replace(DIRECTORY_SEPARATOR, '/', $doc->sourceFilename()));
	        return str_repeat('../', $this->_depth) . 'source/' . $url . '.html#line' . $doc->sourceLine() . '" class="location">' . $doc->location();
	    } else {
	        return $doc->location();
	    }
	}

	/** Write the HTML page to disk using the given path.
	 *
	 * @param str path The path to write the file to
	 * @param str title The title for this page
	 * @param bool shell Include the page shell in the output
	 */
	function _write($path, $title, $shell)
    {
		$phpdoctor =& $this->_doclet->phpdoctor();
		
		// make directory separators suitable to this platform
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		
		// make directories if they don't exist
		$dirs = explode(DIRECTORY_SEPARATOR, $path);
		array_pop($dirs);
		$testPath = $this->_doclet->destinationPath();
		foreach ($dirs as $dir) {
			$testPath .= $dir.DIRECTORY_SEPARATOR;
			if (!is_dir($testPath)) {
                if (!@mkdir($testPath)) {
                    $phpdoctor->error(sprintf('Could not create directory "%s"', $testPath));
                    exit;
                }
            }
		}
		
		// write file
		$fp = fopen($this->_doclet->destinationPath().$path, 'w');
		if ($fp) {
			$phpdoctor->message('Writing "'.$path.'"');
			fwrite($fp, $this->_htmlHeader($title));
			if ($shell) fwrite($fp, $this->_shellHeader($path));
			fwrite($fp, $this->_output);
			if ($shell) fwrite($fp, $this->_shellFooter($path));
			fwrite($fp, $this->_htmlFooter());
			fclose($fp);
		} else {
			$phpdoctor->error('Could not write "'.$this->_doclet->destinationPath().$path.'"');
            exit;
		}
	}
	
	function __removeTextFromMarkup($text) {
		return trim(preg_replace('/<[^>]*>/', '', $text));
	}	
	
	
	function _processTagsHTML(&$tags)
    {
		$tagString = '';
		foreach ($tags as $key => $tag) {
			if ($key != '@text') {
				if (is_array($tag)) {
                    $hasText = FALSE;
                    foreach ($tag as $key => $tagFromGroup) {
                        if ($tagFromGroup->text() != '') {
                            $hasText = TRUE;
                        }
                    }
                    if ($hasText) {
                        $tagString .= '<dt>'.$tag[0]->displayName().":</dt>\n";
                        foreach ($tag as $tagFromGroup) {
                            $tagString .= '<dd>'.$tagFromGroup->text()."</dd>\n";
                        }
                    }
				} else {
					$text = $tag->text();
					if ($text != '') {
						$tagString .= '<dt>'.$tag->displayName().":</dt>\n";
						$tagString .= '<dd>'.$text."</dd>\n";
					} elseif ($tag->displayEmpty()) {
						$tagString .= '<dt>'.$tag->displayName().".</dt>\n";
					}
				}
			}
		}
        if ($tagString) {
            return "<dl>\n" . $tagString . "</dl>\n";
        }
	}
	
	/** Format tags for output.
	 *
	 * @param Tag[] tags
	 * @return str The string representation of the elements doc tags
	 */
	function _processTags(&$tags, $doc=NULL, &$dom_wrapper)
    {
	
		if(is_null($doc)) return '';
	
		$tagString = '';
		
		$found_tags = array();
		
		foreach ($tags as $key => $tag) {
			if ($key != '@text') {
				
				if (is_array($tag)) {
                    $hasText = FALSE;
                    foreach ($tag as $key => $tagFromGroup) {
                        if ($tagFromGroup->text() != '') {
                            $hasText = TRUE;
                        }
                    }
                    if ($hasText) {
						foreach ($tag as $tagFromGroup) {							
							$found_tags[] = array(
								'name' => $tag[0]->displayName(),
								'text' => $tagFromGroup->text(),
								//'type' => $tag->typeName()
							);
                        }
                    }

				} else {
					
					$text = $tag->text();
					if ($text != '') {
						
						$found_tags[] = array(
							'name' => $tag->displayName(),
							'text' => $tag->text()
						);
						
					} elseif ($tag->displayEmpty()) {
						
						$found_tags[] = array(
							'name' => $tag->displayName()
						);
						
					}

				}
			}
		}
		
		$dom_tags = $doc->createElement('tags');
		
		foreach($found_tags as $tag) {
			
			$text = $tag['text'];
			$text = $this->__removeTextFromMarkup($text);
			
			$type_split = explode(' - ', $text);
			
			if (count($type_split) > 1) {
				$type = $type_split[0];
				array_shift($type_split);
				$description = join(' - ', $type_split);
			} else {
				$type = NULL;
				$description = $text;
			}
			
			$dom_tag = $doc->createElement('tag', $description);
			$dom_tag->setAttribute('group', $tag['name']);
			
			$this->parsePackageAndClassFromHyperlink($tag['text'], $dom_tag);
			
			if(!empty($type)) $dom_tag->setAttribute('type', $type);
			
			//$dom_tag->setAttribute('new-type', $tag['type']);
			
			//if(!empty($description) && !empty($type))
			$dom_tags->appendChild($dom_tag);
			
		}
		
		if (count($found_tags) > 0) $dom_wrapper->appendChild($dom_tags);
		
	}
	
	function parsePackageAndClassFromHyperlink($text, &$dom_wrapper) {
		// <a href="../unknown/administrationpage.html#build()">build()</a>
		$matches = array();
		$href = preg_match("/\"(.*)\"/", $text, $matches);
		if (isset($matches[1])) {
			$url = $matches[1];
			$url = trim($url, '.');
			$url = trim($url, '/');
			$package = reset(explode('/', $url));
			$class = reset(explode('.', end(explode('/', $url)) ));
			
			$dom_wrapper->setAttribute('package-handle', $extras['package']);
			$dom_wrapper->setAttribute('class-handle', $extras['class']);
		}
	}
	
	function getSignature($element, $doc, &$dom_wrapper)
    {
		$signature = '';
		$myPackage =& $element->containingPackage();
		foreach($element->_parameters as $param) {
			$type =& $param->type();
			$classDoc =& $type->asClassDoc();
			
			$dom_argument = $doc->createElement('item');
			$dom_argument->setAttribute('name', $param->name());
			$dom_argument->setAttribute('type', $type->typeName());
			
			if ($classDoc) {
				$packageDoc =& $classDoc->containingPackage();
				
				$dom_argument->setAttribute('package', $classDoc->packageName());
				$dom_argument->setAttribute('class', $classDoc->name());
				
				$signature .= '<a href="'.str_repeat('../', $myPackage->depth() + 1).$classDoc->asPath().'">'.$classDoc->name().'</a> '.$param->name().', ';
			} else {
				$signature .= $type->typeName().' '.$param->name().', ';
			}
			
			$dom_wrapper->appendChild($dom_argument);
			
		}
		//return '('.substr($signature, 0, -2).')';
	}
	
	
	/** Convert inline tags into a string for outputting.
	 *
	 * @param Tag tag The text tag to process
	 * @param bool first Process first line of tag only
	 * @return str The string representation of the elements doc tags
	 */
	function _processInlineTags(&$tag, $first = FALSE)
    {
		if ($tag) {
			//$description = '<p>';
			if ($first) {
				$tags =& $tag->firstSentenceTags();
			} else {
				$tags =& $tag->inlineTags();
			}
            if ($tags) {
				foreach ($tags as $aTag) {
					if ($aTag) {
						$tagText = $aTag->text();
						$description .= str_replace("\n\n", ' ', $tagText);
					}
				}
			}
			//$description .= '</p>';
            if ($first) {
                $description = $this->_stripBlockTags($description);
            }
			return $description;
		}
		return NULL;
	}
    
    /** Strip block level HTML tags from a string.
     *
     * @param str string
     * @return str
     */
    function _stripBlockTags($string)
    {
        return strip_tags($string, '<a><b><strong><i><em><code><q><acronym><abbr><ins><del><kbd><samp><sub><sup><tt><var><big><small>');
    }

}

?>

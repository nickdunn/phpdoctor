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

/** This generates the HTML API documentation for each individual interface
 * and class.
 *
 * @package PHPDoctor\Doclets\Standard
 */
class ClassWriter extends HTMLWriter
{

	/** Build the class definitons.
	 *
	 * @param Doclet doclet
	 */
	function classWriter(&$doclet)
    {
	
		parent::HTMLWriter($doclet);
		
		$this->_id = 'definition';

		$rootDoc =& $this->_doclet->rootDoc();
		$phpdoctor =& $this->_doclet->phpdoctor();
		
		$packages =& $rootDoc->packages();
        ksort($packages);

		foreach ($packages as $packageName => $package) {

			/*$this->_sections[0] = array('title' => 'Overview', 'url' => 'overview-summary.html');
			$this->_sections[1] = array('title' => 'Package', 'url' => $package->asPath().'/package-summary.html');
			$this->_sections[2] = array('title' => 'Class', 'selected' => TRUE);
			//$this->_sections[3] = array('title' => 'Use');
			if ($phpdoctor->getOption('tree')) $this->_sections[4] = array('title' => 'Tree', 'url' => $package->asPath().'/package-tree.html');
			if ($doclet->includeSource()) $this->_sections[5] = array('title' => 'Files', 'url' => 'overview-files.html');
			$this->_sections[6] = array('title' => 'Deprecated', 'url' => 'deprecated-list.html');
			$this->_sections[7] = array('title' => 'Todo', 'url' => 'todo-list.html');
			$this->_sections[8] = array('title' => 'Index', 'url' => 'index-all.html');*/
			
			$this->_depth = $package->depth() + 1;
			
			$classes =& $package->allClasses();
			
			if ($classes) {
                ksort($classes);
				foreach ($classes as $name => $class) {
					
					$doc = new DomDocument();
					$dom_class = $doc->createElement('class');
					
					ob_start();
					
					//echo '<div class="qualifiedName">aa', $class->qualifiedName(), "</div>\n";
					$this->_sourceLocation($class);
					
					if ($class->isInterface()) {
						$dom_class->setAttribute('type', 'interface');
					} else {
						$dom_class->setAttribute('type', 'class');
					}
					
					$dom_class->appendChild(
						$doc->createElement('name', $class->name())
					);
					
					$dom_location = $doc->createElement('location', $this->_sourceLocation($class));
					$dom_class->appendChild($dom_location);
					
					$tree = $doc->createElement('tree');
					// TODO
					// echo '<pre class="tree">';
					// 					$result = $this->_buildTree($rootDoc, $classes[$name]);
					// 					echo $result[0];
					// 					echo "</pre>\n\n";
					
					$implements =& $class->interfaces();
					if (count($implements) > 0) {						
						$interfaces = $doc->createElement('interfaces');
						foreach ($implements as $interface) {							
							$dom_interface = $doc->createElement('interface', $interface->name());
							$dom_interface->setAttribute('package', $interface->packageName());
							$dom_interface->setAttribute('path', str_repeat('../', $this->_depth), $interface->asPath());
							$dom_class->appendChild($dom_interface);
						}
					}
					
					$dom_modifiers = $doc->createElement('modifiers');
					foreach(explode(' ', trim($class->modifiers())) as $modifier) {
						$dom_modifiers->appendChild($doc->createElement('modifier', $modifier));
					}
					$dom_class->appendChild($dom_modifiers);
					
					if ($class->superclass()) {
						$superclass =& $rootDoc->classNamed($class->superclass());
						if ($superclass) {
							$dom_superclass = $doc->createElement('superclass', $superclass->name());
						} else {
							$dom_superclass = $doc->createElement('superclass', $class->superclass());
						}
						$dom_class->appendChild($dom_superclass);
					}
					
					$textTag =& $class->tags('@text');
					if ($textTag) {
						$dom_description = $doc->createElement('description', $this->_processInlineTags($textTag));
						$dom_class->appendChild($dom_description);
					}
					
					$dom_class->appendChild($this->_processTags($class->tags(), $doc));

					$constants =& $class->constants();
                    ksort($constants);
					$fields =& $class->fields();
                    ksort($fields);
					$methods =& $class->methods();
                    ksort($methods);

					if ($constants) {
						
						$dom_constants = $doc->createElement('constants');

						foreach ($constants as $field) {
							
							$textTag =& $field->tags('@text');
							
							$dom_constant = $doc->createElement('constant');
							
							$type = $field->modifiers(FALSE) . ' ' . $field->typeAsString();
							$type = $this->__removeTextFromMarkup($type);
							
							$dom_constant->setAttribute('type', $type);
							$dom_constant->setAttribute('name', ((!$field->constantValue()) ? "$" : "") . $field->name());
							if ($field->value()) $dom_constant->setAttribute('value', htmlspecialchars($field->value()));
							
							$dom_constant_location = $doc->createElement('location', $this->_sourceLocation($field));
							$dom_constant->appendChild($dom_constant_location);
							
							if ($textTag) {
								$dom_constant_description = $doc->createElement('description',
									strip_tags($this->_processInlineTags($textTag, TRUE), '<a><b><strong><u><em>')
								);
								$dom_constant->appendChild($dom_constant_description);
							}
							
							$dom_constant->appendChild($this->_processTags($field->tags(), $doc));
							
							$dom_constants->appendChild($dom_constant);
							
						}
						
						$dom_class->appendChild($dom_constants);
					}
					
					if ($fields) {
						
						$dom_fields = $doc->createElement('fields');
						
						foreach ($fields as $field) {
							
							$textTag =& $field->tags('@text');
							
							$dom_field = $doc->createElement('field');
							
							$type = $field->modifiers(FALSE) . ' ' . $field->typeAsString();
							$type = $this->__removeTextFromMarkup($type);
							
							$dom_field->setAttribute('type', $type);
							$dom_field->setAttribute('name', ((!$field->constantValue()) ? "$" : "") . $field->name());
							if ($field->value()) $dom_field->setAttribute('value', htmlspecialchars($field->value()));
							
							$dom_field_location = $doc->createElement('location', $this->_sourceLocation($field));
							$dom_field->appendChild($dom_field_location);
							
							if ($textTag) {
								$dom_field_description = $doc->createElement('description',
									strip_tags($this->_processInlineTags($textTag, TRUE), '<a><b><strong><u><em>')
								);
								$dom_field->appendChild($dom_field_description);
							}
							
							$dom_field->appendChild($this->_processTags($field->tags(), $doc));
							
							$dom_fields->appendChild($dom_field);
						}
						
						$dom_class->appendChild($dom_fields);
						
					}
					
					if ($class->superclass()) {
                        $superclass =& $rootDoc->classNamed($class->superclass());
                        if ($superclass) {
							// TODO
                            $this->inheritFields($superclass, $rootDoc, $package);
                        }
					}

					if ($methods) {
						
						$dom_methods = $doc->createElement('methods');
						
                        foreach($methods as $method) {
                            
							$textTag =& $method->tags('@text');
							
							$dom_method = $doc->createElement('method');
							
							$type = $method->modifiers(FALSE) . ' ' . $method->returnTypeAsString();
							$type = $this->__removeTextFromMarkup($type);
							
							$dom_method->setAttribute('type', $type);
							$dom_method->setAttribute('name', $method->name());
							
							$signature = preg_replace ('/<[^>]*>/', '', $method->flatSignature());
							$signature = trim($signature, ' ()');
							if (!empty($signature)) $dom_method->setAttribute('signature', $signature);
							
							$dom_method_location = $doc->createElement('location', $this->_sourceLocation($method));
							$dom_method->appendChild($dom_method_location);
							
							if ($textTag) {
								$dom_method_description = $doc->createElement('description',
									strip_tags($this->_processInlineTags($textTag, TRUE), '<a><b><strong><u><em>')
								);
								$dom_method->appendChild($dom_method_description);
							}
							
							$dom_method->appendChild($this->_processTags($method->tags(), $doc));
							
							$dom_methods->appendChild($dom_method);
                        }

						$dom_class->appendChild($dom_methods);

					}
					
					if ($class->superclass()) {
                        $superclass =& $rootDoc->classNamed($class->superclass());
                        if ($superclass) {
							// TODO
                            $this->inheritMethods($superclass, $rootDoc, $package);
                        }
					}
					
					$doc->appendChild($dom_class);
					echo $doc->saveXML();
					
					$this->_output = ob_get_contents();
					ob_end_clean();
					
					$this->_write($package->asPath().'/'.strtolower($class->name()).'.xml', $class->name(), TRUE);
				}
			}
		}
    }

	/** Build the class hierarchy tree which is placed at the top of the page.
	 *
	 * @param RootDoc rootDoc The root doc
	 * @param ClassDoc class Class to generate tree for
	 * @param int depth Depth of recursion
	 * @return mixed[]
	 */
	function _buildTree(&$rootDoc, &$class, $depth = NULL)
    {
		if ($depth === NULL) {
			$start = TRUE;
			$depth = 0;
		} else {
			$start = FALSE;
		}
		$output = '';
		$undefinedClass = FALSE;
		if ($class->superclass()) {
			$superclass =& $rootDoc->classNamed($class->superclass());
			if ($superclass) {
				$result = $this->_buildTree($rootDoc, $superclass, $depth);
				$output .= $result[0];
				$depth = ++$result[1];
			} else {
				$output .= $class->superclass().'<br>';
				$output .= str_repeat('   ', $depth).' └─';
				$depth++;
				$undefinedClass = TRUE;
			}
		}
		if ($depth > 0 && !$undefinedClass) {
			$output .= str_repeat('   ', $depth).' └─';
		}
		if ($start) {
			$output .= '<strong>'.$class->name().'</strong><br />';
		} else {
			$output .= '<a href="'.str_repeat('../', $this->_depth).$class->asPath().'">'.$class->name().'</a><br>';
		}
		return array($output, $depth);
	}
	
	/** Display the inherited fields of an element. This method calls itself
	 * recursively if the element has a parent class.
	 *
	 * @param ProgramElementDoc element
	 * @param RootDoc rootDoc
	 * @param PackageDoc package
	 */
	function inheritFields(&$element, &$rootDoc, &$package)
    {
		$fields =& $element->fields();
		if ($fields) {
            ksort($fields);
			$num = count($fields); $foo = 0;
			echo '<table class="inherit">', "\n";
			echo '<tr><th colspan="2">Fields inherited from ', $element->qualifiedName(), "</th></tr>\n";
			echo '<tr><td>';
			foreach($fields as $field) {
				echo '<a href="', str_repeat('../', $this->_depth), $field->asPath(), '">', $field->name(), '</a>';
				if (++$foo < $num) {
					echo ', ';
				}
			}
			echo '</td></tr>';
			echo "</table>\n\n";
			if ($element->superclass()) {
                $superclass =& $rootDoc->classNamed($element->superclass());
                if ($superclass) {
                    $this->inheritFields($superclass, $rootDoc, $package);
                }
			}
		}
	}
	
	/** Display the inherited methods of an element. This method calls itself
	 * recursively if the element has a parent class.
	 *
	 * @param ProgramElementDoc element
	 * @param RootDoc rootDoc
	 * @param PackageDoc package
	 */
	function inheritMethods(&$element, &$rootDoc, &$package)
    {
		$methods =& $element->methods();
		if ($methods) {
            ksort($methods);
			$num = count($methods); $foo = 0;
			echo '<table class="inherit">', "\n";
			echo '<tr><th colspan="2">Methods inherited from ', $element->qualifiedName(), "</th></tr>\n";
			echo '<tr><td>';
			foreach($methods as $method) {
				echo '<a href="', str_repeat('../', $this->_depth), $method->asPath(), '">', $method->name(), '</a>';
				if (++$foo < $num) {
					echo ', ';
				}
			}
			echo '</td></tr>';
			echo "</table>\n\n";
			if ($element->superclass()) {
                $superclass =& $rootDoc->classNamed($element->superclass());
                if ($superclass) {
                    $this->inheritMethods($superclass, $rootDoc, $package);
                }
			}
		}
	}

}

?>

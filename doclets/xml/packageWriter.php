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

/** This generates the package-summary.html files that list the interfaces and
 * classes for a given package.
 *
 * @package PHPDoctor\Doclets\Standard
 */
class PackageWriter extends HTMLWriter
{

	/** Build the package summaries.
	 *
	 * @param Doclet doclet
	 */
	function packageWriter(&$doclet)
    {
	
		parent::HTMLWriter($doclet);
        
		$rootDoc =& $this->_doclet->rootDoc();
		$phpdoctor =& $this->_doclet->phpdoctor();

        $packages =& $rootDoc->packages();
        ksort($packages);

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = FALSE;
		$doc->formatOutput = TRUE;
		
		$dom_packages = $doc->createElement('packages');
        
		foreach($packages as $packageName => $package) {
			
			$dom_package = $doc->createElement('package');
			$dom_package->setAttribute('name', $package->name());
			$dom_package->setAttribute('handle', strtolower($package->name()));
			
			$textTag =& $package->tags('@text');
			if ($textTag) {				
				$dom_package->appendChild($doc->createElement('description', $this->_processInlineTags($textTag)));
			}

			$classes =& $package->ordinaryClasses();
			if ($classes) {
				
				ksort($classes);				
				$dom_items = $doc->createElement('classes');
				
				/*
				A. Recursive nested tree of classes
				*/
				$tree = array();
				foreach ($classes as $class) {
					$this->_buildTree($tree, $class);
				}
				
				$this->_displayTree($tree, NULL, $doc, $dom_items);
				
				/*
				B. Non-nested list of classes
				
				foreach($classes as $name => $class) {
					
					$description = NULL;
					$textTag =& $classes[$name]->tags('@text');
					if ($textTag) $description = $this->_processInlineTags($textTag);
					
					$dom_item = $doc->createElement('class', $description);
					$dom_item->setAttribute('name', $classes[$name]->name());
					$dom_item->setAttribute('path', str_repeat('../', $this->_depth), $classes[$name]->asPath());
					
					$dom_items->appendChild($dom_item);
				}				
				*/
				
				$dom_package->appendChild($dom_items);
				
			}

			$interfaces =& $package->interfaces();
			if ($interfaces) {
                ksort($interfaces);

				$dom_items = $doc->createElement('interfaces');
				
				foreach($interfaces as $name => $interface) {
					
					$description = NULL;
					$textTag =& $interfaces[$name]->tags('@text');
					if ($textTag) $description = $this->_processInlineTags($textTag);
					
					$dom_item = $doc->createElement('interface', $description);
					$dom_item->setAttribute('name', $interfaces[$name]->name());
					$dom_item->setAttribute('path', str_repeat('../', $this->_depth), $interfaces[$name]->asPath());
					
					$dom_items->appendChild($dom_item);
				}
				
				$dom_package->appendChild($dom_items);

			}

			$exceptions =& $package->exceptions();
			if ($exceptions) {
                ksort($exceptions);

				$dom_items = $doc->createElement('exceptions');
				
				foreach($exceptions as $name => $exception) {
					
					$description = NULL;
					$textTag =& $exceptions[$name]->tags('@text');
					if ($textTag) $description = $this->_processInlineTags($textTag);
					
					$dom_item = $doc->createElement('exception', $description);
					$dom_item->setAttribute('name', $exceptions[$name]->name());
					$dom_item->setAttribute('path', str_repeat('../', $this->_depth), $exceptions[$name]->asPath());
					
					$dom_items->appendChild($dom_item);
				}
				
				$dom_package->appendChild($dom_items);
			}
			
			$functions =& $package->functions();
			if ($functions) {
                ksort($functions);

				$dom_items = $doc->createElement('functions');
				
				foreach($functions as $name => $function) {
					
					$description = NULL;
					$textTag =& $functions[$name]->tags('@text');
					if ($textTag) $description = $this->_processInlineTags($textTag);
					
					$dom_item = $doc->createElement('function', $description);
					$dom_item->setAttribute('name', $functions[$name]->name());
					$dom_item->setAttribute('path', str_repeat('../', $this->_depth), $functions[$name]->asPath());
					
					$dom_items->appendChild($dom_item);
				}
				
				$dom_package->appendChild($dom_items);
			}
			
			$globals =& $package->globals();
			if ($globals) {
                ksort($globals);

				$dom_items = $doc->createElement('globals');
				
				foreach($globals as $name => $global) {
					
					$description = NULL;
					$textTag =& $globals[$name]->tags('@text');
					if ($textTag) $description = $this->_processInlineTags($textTag);
					
					$dom_item = $doc->createElement('global', $description);
					$dom_item->setAttribute('name', $globals[$name]->name());
					$dom_item->setAttribute('path', str_repeat('../', $this->_depth), $globals[$name]->asPath());
					
					$dom_items->appendChild($dom_item);
				}
				
				$dom_package->appendChild($dom_items);

			}
						
			$dom_packages->appendChild($dom_package);
			
		}
		
		$doc->appendChild($dom_packages);
		
		$this->_output = $doc->saveXML();
		$this->_write('packages.xml', 'Packages', FALSE);

	
	}
	
	/**
	 * Build the class tree branch for the given element
	 *
	 * @param ClassDoc[] tree
	 * @param ClassDoc element
	 */
	function _buildTree(&$tree, &$element)
    {
		$tree[$element->name()] = $element;
		if ($element->superclass()) {
			$rootDoc =& $this->_doclet->rootDoc();
            $superclass =& $rootDoc->classNamed($element->superclass());
            if ($superclass) {
                $this->_buildTree($tree, $superclass);
            }
		}
	}
	
	/**
	 * Build the class tree branch for the given element
	 *
	 * @param ClassDoc[] tree
	 * @param str parent
	 */
	function _displayTree($tree, $parent=NULL, $doc, &$dom_wrapper)
    {
		$outputList = TRUE;
		foreach($tree as $name => $element) {
			if ($element->superclass() == $parent) {
				
				$description = NULL;
				$textTag =& $element->tags('@text');
				if ($textTag) $description = $this->_processInlineTags($textTag);

				$dom_item = $doc->createElement('class', $description);
				$dom_item->setAttribute('name', $element->name());
				$dom_item->setAttribute('handle', strtolower($element->name()));
				
				$this->_displayTree($tree, $name, $doc, $dom_item);
				
				$dom_wrapper->appendChild($dom_item);
				
			}
		}
		
	}

}

?>

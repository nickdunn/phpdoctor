<?php

class DeprecatedWriter extends HTMLWriter {

	function deprecatedWriter(&$doclet) {
	
		parent::HTMLWriter($doclet);
		
		$rootDoc =& $this->_doclet->rootDoc();
		
		$doc = new DomDocument();
		$doc->preserveWhiteSpace = FALSE;
		$doc->formatOutput = TRUE;
		
		$dom_deprecated = $doc->createElement('deprecated');
		
        $deprecatedClasses = array();
        $deprecatedFields = array();
        $deprecatedMethods = array();
		$deprecatedConstants = array();
		$deprecatedGlobals = array();
		$deprecatedFunctions = array();
		
		$classes =& $rootDoc->classes();
        if ($classes) {
            foreach ($classes as $class) {
                if ($class->tags('@deprecated')) $deprecatedClasses[] = $class;
                $fields =& $class->fields();
                if ($fields) {
                    foreach ($fields as $field) {
                        if ($field->tags('@deprecated')) $deprecatedFields[] = $field;
                    }
                }
                $classes =& $class->methods();
                if ($classes) {
                    foreach ($classes as $method) {
                        if ($method->tags('@deprecated')) $deprecatedMethods[] = $method;
                    }
                }
				$constants =& $class->constants();
                if ($constants) {
                    foreach ($constants as $constant) {
                        if ($constant->tags('@deprecated')) $deprecatedConstants[] = $constant;
                    }
                }
            }
        }
        
        $globals =& $rootDoc->globals();
        if ($globals) {
            foreach ($globals as $global) {
                if ($global->tags('@deprecated')) $deprecatedGlobals[] = $global;
            }
        }
        
        $functions =& $rootDoc->functions();
        if ($functions) {
            foreach ($functions as $function) {
                if ($function->tags('@deprecated')) $deprecatedFunctions[] = $function;
            }
        }
        
        if ($deprecatedClasses) {
	
			$dom_list = $doc->createElement('classes');
	
            foreach($deprecatedClasses as $item) {
	
				$textTag =& $item->tags('@text');
				$description = '';
				if ($textTag) $description = $this->_processInlineTags($textTag);
				
				$dom_item = $doc->createElement('class', $description);
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);

        }

		if ($deprecatedFields) {
	
			$dom_list = $doc->createElement('fields');
	
            foreach($deprecatedFields as $item) {
	
				$textTag =& $item->tags('@text');
				$description = '';
				if ($textTag) $description = $this->_processInlineTags($textTag);
				
				$dom_item = $doc->createElement('field', $description);
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('class', $item->containingClass()->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
        }
        
        if ($deprecatedConstants) {
	
			$dom_list = $doc->createElement('constants');
	
            foreach($deprecatedConstants as $item) {
	
				$textTag =& $item->tags('@text');
				$description = '';
				if ($textTag) $description = $this->_processInlineTags($textTag);
				
				$dom_item = $doc->createElement('constant', $description);
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('class', $item->containingClass()->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
        }
        
        if ($deprecatedMethods) {
		
			$dom_list = $doc->createElement('methods');
	
            foreach($deprecatedMethods as $item) {
	
				$textTag =& $item->tags('@text');
				$description = '';
				if ($textTag) $description = $this->_processInlineTags($textTag);
				
				$dom_item = $doc->createElement('method', $description);
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('class', $item->containingClass()->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
	
        }
        
        if ($deprecatedGlobals) {
	
			$dom_list = $doc->createElement('globals');
	
            foreach($deprecatedGlobals as $item) {
	
				$textTag =& $item->tags('@text');
				$description = '';
				if ($textTag) $description = $this->_processInlineTags($textTag);
				
				$dom_item = $doc->createElement('global', $description);
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
		}
        
        if ($deprecatedFunctions) {
            
			$dom_list = $doc->createElement('functions');
	
            foreach($deprecatedFunctions as $item) {
	
				$textTag =& $item->tags('@text');
				$description = '';
				if ($textTag) $description = $this->_processInlineTags($textTag);
				
				$dom_item = $doc->createElement('function', $description);
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
        }

		$doc->appendChild($dom_deprecated);
        $this->_output = $doc->saveXML();
        $this->_write('deprecated.xml', 'Deprecated', TRUE);
	
	}
  
}
<?php

class tx_bahag_photogallery_common {
	
	/**
	 * Get the html sub template from main template file
     *
	 * @access public
	 * @param string $template - HTML Template
	 * @param string $marker - Marker for sub template
	 * @return string
	 */	
	function getSubTemplate( $template, $marker = '') {
		$subTemplate = '';
		
		if ( ($template !== '') && ($maker !== '')) {
    		$pattern = '/<!--###'.$marker.'###(([a-zA-Z])|(\s)|(\t))*-->(.*)<!--###'.$marker.'###(([a-zA-Z])|(\s)|(\t))*-->/si';
    		preg_match($pattern, $template, $subTemplate);

    		$subTemplate = $subTemplate[0];
    		$subTemplate = preg_replace('/<!--###'.$marker.'###(.)*-->/i', '', $subTemplate);
		}
		
		return $subTemplate;
	}

	/**
	 * Get the html sub template from main template file
	 *
	 * @access public
	 * @param string $template - HTML Template
	 * @param string $replace - Array containing substitute for template markers
	 * @param string $inPair - Flag to know whether markers are in pair or not 
	 * @return string
	 */	
	function replaceTplMarkers( $template, $replace, $inPair = false) {
		$content = '';
		
		if ( $template !== '') {
			$content = $template;
			if ( is_array( $replace) && !empty( $replace)) {
				foreach ( $replace as $key => $value) {
					if ( $inPair) {
						$pattern = '/<!--###'.$key.'###(([a-zA-Z])|(\s)|(\t))*-->(.*)<!--###'.$key.'###(([a-zA-Z])|(\s)|(\t))*-->/si';
					} else {
						$pattern = '/###'.$key.'###/i';
					}

					$content = preg_replace($pattern, $value, $content);
				}
			}
		}
    		/*debug( $content);
    		exit();*/

		return $content;
	}	
}

?>

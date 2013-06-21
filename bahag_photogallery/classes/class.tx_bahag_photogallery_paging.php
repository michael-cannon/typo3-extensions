<?php
class tx_bahag_photogallery_paging {

	/**
	* Number of images to display in gallery
	* @var integer
	*/
	var $pageSize;
	
	/**
	* Result page for the current gallery
	* @var integer
	*/
	var $page;
	
	/**
	* Count of the images in current gallery
	* @var integer
	*/
	var $itemCount;



	/**
	* Initialize paging class variables.
	* @param string $resultPage - Current page to display for gallery
	* @param string $itemCount - Number of images in gallery
	* @param string $pageSize - Number of images to dislay at a time in gallery
	* @access private
	*/	

	function tx_bahag_photogallery_paging ( $resultPage, $itemCount, $pageSize) {
		$this->itemCount = $itemCount;
		$this->pageSize = $pageSize;

		if ($resultPage < 1) {
			$resultPage = 1;
		}
		
		if ($resultPage > $this->getNumPages()) {
			$resultPage = $this->getNumPages();
		}
		
		$this->setPageNum($resultPage);
	}


	/**
	* Get the number of pages for gallery
	* @return boolean
	* @param string $imagePath - Absolute path of the image to process
	* @param string $conf - Configuration settings for new image
	* @access private
	*/	
	function getNumPages() {
		return ceil($this->itemCount / (float)$this->pageSize);
	}


	/**
	* set current page number for the gallery
	* @param string $pageNum - Page number to set
	* @access private
	*/	
	function setPageNum( $pageNum) {
		if (($pageNum > $this->getNumPages()) || ($pageNum <= 0)) {
			return false;
		}

		$this->page = $pageNum;
	}


	/**
	* get current page number for the gallery
	* @return integer
	* @access private
	*/	

	function getPageNum() {
		return $this->page;
	}


	/**
	* Function to check whether the current page is the last page of gallery or not
	* @return boolean
	* @access private
	*/	

	function isLastPage() {
		return ($this->page >= $this->getNumPages());
	}


	/**
	* Function to check whether the current page is the first page of gallery or not
	* @return boolean
	* @access private
	*/	

	function isFirstPage() {
		return ($this->page <= 1);
	}


	/**
	* Get the navigation details for the current gallery
	* @return array
	* @access private
	*/	
	function getPageNav() {
		$nav = array();
		if (!$this->isFirstPage()) {
			$nav['prev']['resultPage'] = $this->getPageNum()-1;
		}

		if ($this->getNumPages() > 1) {
			for ($i = 1; $i <= $this->getNumPages(); $i++) {
				if ($i == $this->page) {
					$nav['curr']['resultPage'] = $i;
				}
				$nav[$i]['resultPage'] = $i;
			}
		}

		if (!$this->isLastPage()) {
			$nav['next']['resultPage'] = $this->getPageNum()+1;
		}

		return $nav;
	}
}
?>

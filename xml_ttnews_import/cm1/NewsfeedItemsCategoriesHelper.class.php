<?php 

require_once('AssociationHelper.class.php');

/**
Assists in updating the categories associated with an imported newsfeed item.
Normal use:
$newsfeedItemsCategoriesHelper->propagateNewsfeedCategoriesToItem($newsfeedID, $newsItemID)
$Id: NewsfeedItemsCategoriesHelper.class.php,v 1.1.1.1 2010/04/15 10:04:15 peimic.comprock Exp $
*/
class NewsfeedItemsCategoriesHelper {
	
	var $newsFeedCategoriesHelper;
	var $newsItemCategoriesHelper;
	
	/** Whether to print debugging info */
	var $debug = false;
	
	/** Constructor
	*/
	function NewsfeedItemsCategoriesHelper() {
		
		if ($this->debug) {echo "NewsfeedItemsCategoriesHelper()\n"; } 
		
		//Set up the helper for newsfeed definitions and categories
		$localTable1 		= 'tx_ccrdfnewsimport';
		$foreignTable1 	= 'tt_news_cat';
		$linkTable1 		= 'tx_ccrdfnewsimport_tx_xmlttnewsimport_category_mm';
		//$linkTableLocalField = "uid_local"
		//$linkTableForeignField = "uid_foreign"
		$this->newsFeedCategoriesHelper = new AssociationHelper( 
			$localTable1, 
			$foreignTable1, 
			$linkTable1
			);
		
		//Set up the helper for news items and categories
		$localTable2 		= 'tt_news';
		$foreignTable2 	= 'tt_news_cat';
		$linkTable2 		= 'tt_news_cat_mm';
		//$linkTableLocalField = "uid_local"
		//$linkTableForeignField = "uid_foreign"
		$this->newsItemCategoriesHelper = new AssociationHelper( 
			$localTable2, 
			$foreignTable2, 
			$linkTable2
			);
			
		if ($this->debug) {
			echo '$this->newsFeedCategoriesHelper '; 
			var_dump($this->newsFeedCategoriesHelper);
			
			echo '$this->newsItemsCategoriesHelper '; 
			var_dump($this->newsItemCategoriesHelper);
		}

	}
	
	/**
	Return the IDs of the news categories associated with the specified newsfeed
	definition.
	 
	Usage:
	<code>
	$categories = $newsFeedItemCategoriesHelper->getCategoriesForNewsfeedDefinition($newsfeedID)
	</code>
	
	@param string the newsfeed definition id.  (Normally, a UID from table
		cc_rdf_news_import)
	@return array. The categories
	*/
	function getCategoriesForNewsfeedDefinition($newsfeedID) {
		
		assert(!empty($newsfeedID));
		$categories = $this->newsFeedCategoriesHelper->getAssociations($newsfeedID);
		return $categories;
		
	}
	
	/**
	Associaite the specified news categories with the specified news item.
	
	The news item is a row normally in tt_news.
	The news category normally is a row in tt_news_cat.
	The linking table for associating these is normally tt_news_cat_mm.
	 
	Usage:
	<code>
	$newsItemCategoriesHelper->setCategoriesForNewsItem( $nitem['uid'], $categories );
	</code>
	
	@param string the news item id.  (Normally, a UID from table tt_news.)
	@param array the news categories. (Normally, UIDs from table tt_news_cat.)
	@return none
	*/
	function setCategoriesForNewsItem( $newsItemID, $categories ) {
		
		assert(!empty($newsItemID));
		
		foreach ($categories as $category) {
			$this->newsItemCategoriesHelper->addAssociation( $newsItemID, $category );
		}
		
	}
	
	
	/**
	Convenience method to associate the categories for a newsfeed definition with
	a news item in one step.
	
	@param string the newsfeed definition id.  (Normally, a UID from table
		cc_rdf_news_import)
	@param string the news item id.  (Normally, a UID from table tt_news.)
	*/
	function propagateNewsfeedCategoriesToItem($newsfeedID, $newsItemID) {
		
		$categories = $this->getCategoriesForNewsfeedDefinition($newsfeedID);
		$this->setCategoriesForNewsItem( $newsItemID, $categories );
		
	}
	

}


?>

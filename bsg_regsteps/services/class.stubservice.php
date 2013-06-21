<?php


class StubService {
    var $months					= array();
    var $defaultCode			= 'DEFAULT';
    var $courseTypeConference	= 'conference';
    var $courseTypeEvent		= 'events';
    var $courseTypeTraining		= 'training';
	var $confSeries				= array();
	var $confSeriesTrainingOnly	= '';
    
    function StubService() {
		require( dirname( __FILE__ )."/../config/" . BSG_REG_CONF_CODE . ".config.php");
        $this->courses			= $courses;
		$this->confSeries		= $confSeries;
		$this->confSeriesTrainingOnly	= $confSeriesTrainingOnly;

		require(dirname( __FILE__ )."/../config/" . BSG_REG_CONF_CODE . ".priority-codes.php");
        $this->priorityCodes	= $priorityCodes;

        $this->months = array(
			array('uid'=>1, 'name'=>'January'),
			array('uid'=>2, 'name'=>'February'),
			array('uid'=>3, 'name'=>'March'),
			array('uid'=>4, 'name'=>'April'),
			array('uid'=>5, 'name'=>'May'),
			array('uid'=>6, 'name'=>'June'),
			array('uid'=>7, 'name'=>'July'),
			array('uid'=>8, 'name'=>'August'),
			array('uid'=>9, 'name'=>'September'),
			array('uid'=>10, 'name'=>'October'),
			array('uid'=>11, 'name'=>'November'),
			array('uid'=>12, 'name'=>'December'),
        );

		$currentYear = date('y');
        $this->years = range($currentYear, $currentYear+11);
        array_map('strval', $this->years);
        for($i=0,$iCount = count($this->years); $i<$iCount; $i++) {
            if( strlen(strval($this->years[$i])) == 1) {
                $this->years[$i] = "0".$this->years[$i];
            }
        }
    }


    function fillCoursePrice($code, &$course) {
        $type = $course['type'];
        $course['price'] = $this->getPrice($code, $type, $course['uid']);
    }


    function getCoursesWithPrices($code, $cSource = null) {
        $arrOut = array();
        if($cSource == null) {
            $cSource =& $this->courses;
        }
        foreach ($cSource as $group) {
            $courses = array();
            foreach ($group['courses'] as $course) {
                $type = $course['type'];
        		$course['price'] = $this->getPrice($code, $type, $course['uid']);
                $courses[] = $course;
            }
            $arrOut[] = array(
				'title' => $group['title'],
				'subtitle' => $group['subtitle'],
				'thankyoutitle' => $group['thankyoutitle'],
				'background' => $group['background'],
				'coursetype' => $group['type'],
				'courses' => $courses
			);
        }
        return $arrOut;
    }

    
    function getAutoBuypro($code) {
        $type = 'auto-buypro';
		return $this->getPrice($code, $type);
    }

    
    function getProPrice($code, $buypro) {
        $type					= ( 2 != $buypro )
									? 'pro-membership'
									: 'pro-renewal'
								;
		return $this->getPrice($code, $type);
    }

    function getPriorityCodes() {
        return $this->priorityCodes;
    }

    function getCourses() {
        return $this->courses;
    }

    function getCourseByUid($uid) {
        foreach ($this->courses as $row) {
            foreach ($row['courses'] as $r) {
                if($r['uid'] == $uid) {
					// MLC 20080130 add course headings
					$r['title']		= $row['title'];
					$r['subtitle']	= $row['subtitle'];
					$r['thankyoutitle']	= $row['thankyoutitle'];
					$r['coursetype']	= $row['type'];
                    return $r;
                }
            }
        }
        return false;
    }

    function getCoursesByUid($uids, $code = null) {
		// reset purchaseCount to prevent off-by-one or accidental chained
		// pricing
		if ( isset($_SESSION['bsg_regsteps']['purchaseCount']) ) {
			unset($_SESSION['bsg_regsteps']['purchaseCount']);
		}

        $arrOut = array();
        foreach ($uids as $uid) {
            $data = $this->getCourseByUid($uid);
            if($data !== false) {
                if($code !== null) {
	                $this->fillCoursePrice($code, $data);
                }
                $arrOut[] = $data;
            }
        }
        return $arrOut;
    }

    function getCoursesGroupCount() {
        return count($this->courses);
    }

    function getMonths() {
        return $this->months;
    }

    function getYears() {
        return $this->years;
    }

    function getCoursesBreakdown( $code ) {
		$arrOut					= array(
									$this->courseTypeConference => array()
									, $this->courseTypeEvent => array()
									, $this->courseTypeTraining => array()
								);

        foreach ($this->courses as $group) {
			$type				= $group['type'];
            $courses = array();

            foreach ($group['courses'] as $course)
			{
				$uid			= $course['uid'];
				$ctype			= $course['type'];

				$price			= $this->getPrice($code, $ctype, $uid);

				$course			= array(
									'uid' => $uid
									, 'price' => $price
									, 'type' => $ctype
								);

                if ( $this->courseTypeTraining == $type ) {
					$arrOut[$this->courseTypeTraining][$uid]	= $course;
                } elseif ( $this->courseTypeConference == $type ) {
					$arrOut[$this->courseTypeConference][$uid]		= $course;
                } else {
					$arrOut[$this->courseTypeEvent][$uid]		= $course;
                }
            }
        }

        return $arrOut;
    }
	
	function getPrice($code, $type, $id = false) {
		$id = $id
			? preg_replace( "#\w+-#", '', $id)
			: $id;

		if($id && isset($this->priorityCodes[$code][$id])) {
        	return floatval( $this->getPriorityCodePrice($code, $id) );
		} elseif (isset($this->priorityCodes[$code][$type])) {
        	return floatval( $this->getPriorityCodePrice($code, $type) );
		} else {
        	return floatval( $this->getPriorityCodePrice($this->defaultCode, $type) );
		}
	}

	function getPriorityCodePrice($code, $type) {
		$prices = $this->priorityCodes[$code][$type];

		if ( stristr($prices, '|') ) {
			if ( ! isset($_SESSION['bsg_regsteps']['purchaseCount'][$code][$type]) ) {
				$purchaseCount = 0;
			} else {
				$purchaseCount = $_SESSION['bsg_regsteps']['purchaseCount'][$code][$type];
			}

			$_SESSION['bsg_regsteps']['purchaseCount'][$code][$type]++;

			$prices = explode('|', $prices);

			if  ( isset($prices[$purchaseCount]) ) {
				return floatval( $prices[$purchaseCount] );
			} else {
				// return the last price in case of only alternate pricing for
				// preset quantities
				return floatval( array_pop( $prices ) );
			}
		}

		return floatval( $prices );
	}
}

?>
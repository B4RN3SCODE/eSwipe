<?php
#region intro
/***********************************************************
 * eSwipeImapSearchBuilder
 * Builds search criteria string to make email searching
 * easier
 *
 * @author			Tyler J Barnes
 * @contact			tylerb@conversionvoodoo.com
 * @version			0.0.1
 **********************************************************/
#endregion

#region class_todo
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * TODO
 *
 * 20160217 - Tyler J Barnes
 * 	- init dev
 *
 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
#endregion

#region eSwipeImapSearchBuilder
class eSwipeImapSearchBuilder {

	#region props

	// array of criteria/values
	public $_criteria = array();

	// criteria map
	private $_criteriaMap = array();

	// error/invalidations
	private $_invalidations = array();

	// criteria string
	private $_criteriaString;

	// tracks if string is built
	private $_isBuilt;

	#endregion



	#region c'tor
	/*******************************************************
	 * c'tor
	 *
	 * @param criteria (array) crieteria ['CRITERIA'=>'VAL']
	 * @return void
	 *******************************************************/
	public function eSwipeImapSearchBuilder(array $criteria = array()) {

		$this->_criteria = $criteria;
		$this->_invalidations = array();
		$this->_criteriaString = "";
		$this->_isBuilt = false;

		$this->_criteriaMap = array(
			"ALL"			=>		array("var"	=>	null, "qry"	=>	"ALL"),
			"ANSWERED"		=>		array("var"	=>	null, "qry"	=>	"ANSWERED"),
			"BCC"			=>		array("var"	=>	"email", "qry"	=>	"BCC \"{email}\""),
			"BEFORE"		=>		array("var"	=>	"date", "qry"	=>	"BEFORE \"{date}\""),
			"BODY"			=>		array("var"	=>	"text", "qry"	=>	"BODY \"{text}\""),
			"CC"			=>		array("var"	=>	"email", "qry"	=>	"CC \"{email}\""),
			"DELETED"		=>		array("var"	=>	null, "qry"	=>	"DELETED"),
			"FLAGGED"		=>		array("var"	=>	null, "qry"	=>	"FLAGGED"),
			"FROM"			=>		array("var"	=>	"name", "qry"	=>	"FROM \"{name}\""),
			"KEYWORD"		=>		array("var"	=>	"word", "qry"	=>	"KEYWORD \"{word}\""),
			"NEW"			=>		array("var"	=>	null, "qry"	=>	"NEW"),
			"OLD"			=>		array("var"	=>	null, "qry"	=>	"OLD"),
			"ON"			=>		array("var"	=>	"date", "qry"	=>	"ON \"{date}\""),
			"RECENT"		=>		array("var"	=>	null, "qry"	=>	"RECENT"),
			"SEEN"			=>		array("var"	=>	null, "qry"	=>	"SEEN"),
			"SINCE"			=>		array("var"	=>	"date", "qry"	=>	"SINCE \"{date}\""),
			"SUBJECT"		=>		array("var"	=>	"text", "qry"	=>	"SUBJECT \"{text}\""),
			"TEXT"			=>		array("var"	=>	"text", "qry"	=>	"TEXT \"{text}\""),
			"TO"			=>		array("var"	=>	"email", "qry"	=>	"TO \"{email}\""),
			"UNANSWERED"	=>		array("var"	=>	null, "qry"	=>	"UNANSWERED"),
			"UNDELETED"		=>		array("var"	=>	null, "qry"	=>	"UNDELETED"),
			"UNFLAGGED"		=>		array("var"	=>	null, "qry"	=>	"UNFLAGGED"),
			"UNKEYWORD"		=>		array("var"	=>	"word", "qry"	=>	"UNKEYWORD \"{word}\""),
			"UNSEEN"		=>		array("var"	=>	null, "qry"	=>	"UNSEEN"),
		);

	}
	#endregion



	#region accessors

	/***********************************
	 * getCriteria
	 * @return array of criteria
	 **********************************/
	public function getCriteria() { return $this->_criteria; }


	/***********************************
	 * getInvalidationErrors
	 * @return array of invalidations
	 **********************************/
	public function getInvalidationErrors() { return $this->_invalidations; }



	/***********************************
	 * IsBuilt
	 * @return true if already built
	 **********************************/
	public function IsBuilt() { return $this->_isBuilt; }



	/***********************************
	 * getCriteriaString
	 * @return criteria string
	 **********************************/
	public function getCriteriaString() { return $this->_criteriaString; }



	/**********************************
	 * setCriteria
	 * @param criteria (array) of criteria
	 * @return void
	 * @throws Exception
	 **********************************/
	public function setCriteria(array $criteria = array()) {

		if(is_array($criteria) && count($criteria) > 0) {

			$this->_criteria = $criteria;
			$this->_isBuilt = false;

		} else {

			throw new Exception("Invalid criteria passed eSwipeImapSearchBuilder::setCriteria");

		}
	}



	/**********************************
	 * setCriteriaString
	 * @param str (string) built string
	 * @return void
	 **********************************/
	private function setCriteriaString($str) {
		$this->_criteriaString = trim($str);
	}


	#endregion



	#region functions

	/*******************************************
	 * BuildCriteriaString
	 * Builds a string of criteria to search for
	 *
	 * @return void
	 * @throws Exception
	 ******************************************/
	public function BuildCriteriaString() {
		// if its already built do nothing
		if(!$this->IsBuilt()) {

			// check for criteria ... if not exist throw exception
			if(isset($this->_criteria) && count($this->_criteria) > 0) {

				// start building string
				$str = "";
				// iterate through the criteria and build string
				foreach($this->_criteria as $field => $value) {

					$field = strtoupper($field);

					// if field not found in map, its invalid
					if(!isset($this->_criteriaMap[$field])) {
						$this->_invalidations[] = "Field [{$field}] skipping invalid field";
						continue;
					}


					// if field var is empty, then just add the query string
					if(is_null($this->_criteriaMap[$field]["var"])) {

						$str .= " {$this->_criteriaMap[$field]["qry"]}";

					// otherwise, we must parse the query string and
					// replace the var with the value
					} else {

						// make sure there is a value
						if(!isset($value) || empty($value) || is_null($value)) {
							$this->_invalidations[] = "Field [{$field}] error parsing value";
							continue;
						}

						// replace the query string template with the value
						// from the criteria
						$parsed = str_replace("{{$this->_criteriaMap[$field]["var"]}}", $value, $this->_criteriaMap[$field]["qry"]);
						// append parsed string
						$str .= " {$parsed}";

					}


				} // END FOREACH

				// set value
				$this->setCriteriaString($str);
				$this->_isBuilt = true;


			// if no criterial throw excep
			} else {

				throw new Exception("Criteria not provided eSwipeImapSearchBuilder::BuildCriteriaString");

			} // END ELSE


		} // END IsBuilt IF


	}



	#endregion



}
#endregion

////////////////////////////////////////////////////////////////////
/////////////////         end          /////////////////////////////
////////////////////////////////////////////////////////////////////
?>

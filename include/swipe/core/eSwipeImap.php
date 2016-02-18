<?php
#region dependencies
include_once("include/swipe/config/config.php");
include_once("eSwipeImapSearchBuilder.php");
include_once("eSwipeImapEmailHandler.php");
#endreadion

#region intro
/***********************************************************
 * eSwipeImap
 * IMAP object wrapper for phps normal imap functions for
 * an eSwipe OOP system
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

#region eSwipeImap
class eSwipeImap {

	#region props

	// mailbox string
	protected $_mailBoxStr;

	// username to use for connection
	private $_userName;

	// password
	private $_password;

	// number of retries
	protected $_numRetries;

	// mailbox reference
	private $_mailBox;

	// tracks if connection is open
	private $_opened;

	// search builder object to use
	private $_searcher;

	// email handler
	private $_emailHandler;

	// email list
	public $_emailList;

	// imap_errors
	private $_imapErrors;

	// exceptions stack
	private $_exceptions;

	// STATIC property default search criteria for email query
	protected static $DEFAULT_SEARCH_CRITERIA = array("ALL"=>null,"UNSEEN"=>null);

	#endregion



	#region c'tor
	/****************************************************
	 * c'tor
	 *
	 * @param mailboxstr (string)
	 * @param retries (int) number of auth/conn retries
	 * @return void
	 ****************************************************/
	public function eSwipeImap($mailboxstr = "", $retries = 0) {

		// set mailbox string based on param
		// set defaults based on config values
		$this->_mailBox = (isset($mailboxstr) && !empty($mailboxstr)) ? $mailboxstr : IMAP_MAILBOX_STR;
		$this->_userName = IMAP_ACC_USR;
		$this->_password = IMAP_ACC_PWD;
		$this->_numRetries = $retries;
		$this->_mailBox = null;
		$this->_opened = false;
		$this->_searcher = null;
		$this->_emailHandler = null;
		$this->_emailList = array();

	}
	#endregion



	#region accessors

	/*******************************************************
	 * getMailBoxStr
	 * @return (string) mail box string
	 ******************************************************/
	public function getMailBoxStr() { return $this->_mailBoxStr; }


	/*******************************************************
	 * getNumRetries
	 * @return (int) number of retries
	 ******************************************************/
	public function getNumRetries() { return (isset($this->_numRetries) && !empty($this->_numRetries) && !is_null($this->_numRetries)) ? $this->_numRetries : 0; }


	/*******************************************************
	 * IsConnected
	 * @return (bool) true if connected
	 ******************************************************/
	public function IsConnected() { return $this->_opened; }



	/*******************************************************
	 * getImapErrors
	 * @return errors
	 ******************************************************/
	public function getImapErrors() { $this->_imapErrors = imap_errors(); return $this->_imapErrors; }



	/*****************************************************
	 * getEmailList
	 * @return array of emails
	 *****************************************************/
	public function getEmailList() { return $this->_emailList; }




	/*******************************************************
	 * setMailBoxStr
	 * @param str (string) the string
	 * @return void
	 *******************************************************/
	public function setMailBoxStr($str = "") { $this->_mailBoxStr = $str; }


	/*******************************************************
	 * setNumRetries
	 * @param num (int) number of retries
	 * @return void
	 *******************************************************/
	public function setNumRetries($num = 0) { $this->_numRetries = $num; }


	/********************************************************
	 * setMailBox
	 * @param box (resource) resource to mailbox
	 * @return void
	 *******************************************************/
	private function setMailBox($box = null) {
		if(is_resource($box) && strtolower(get_resource_type($box)) == "imap") {
			$this->_mailBox = $box;
		}
	}


	#endregion



	#region functions

	/*********************************************
	 * Open
	 * Opens an IMAP connection and stores the
	 * resource as mailBox property
	 *
	 * @return true if successful
	 ********************************************/
	public function Open() {
		if($this->IsConnected()) {
			return true;
		}

		if(isset($this->_numRetries) && $this->_numRetries > 0) {

			$box = imap_open(IMAP_MAILBOX_STR, IMAP_ACC_USR, IMAP_ACC_PWD, '', $this->_numRetries);

		} else {

			$box = imap_open(IMAP_MAILBOX_STR, IMAP_ACC_USR, IMAP_ACC_PWD);

		}

		if($box !== false) {
			$this->setMailBox($box);
			$this->_opened = true;
		}

		return $this->IsConnected();
	}




	/***************************************************
	 * Search
	 * Search for emails based on criteria passed - or
	 * using default search criteria
	 *
	 * @param criteria (array) search options
	 * @return array of email results
	 * @throws Exception
	 ***************************************************/
	public function Search(array $criteria = array(), $return_search = true) {

		if(!isset($criteria) || empty($criteria)) {
			$criteria = self::$DEFAULT_SEARCH_CRITERIA;
		}
		// make sure we are connected
		if(!$this->IsConnected()) {
			// report connect failure
			if(!$this->Open()) {
				throw new Exception("Cannot establish IMAP connection eSwipeImap::Search");
			}
		}

		$result = array();

		try {

			$this->PrepareSearch($criteria);
			// imap_search
			$email_result = imap_search($this->_mailBox, $this->_searcher->getCriteriaString());

			if($email_result !== false) {
				$result = $email_result;
			}

		} catch(Exception $e) {
			throw $e;
		}

		$this->_emailList = $result;

		if($return_search === true) {
			return $this->_emailList;
		}

	}



	/**************************************
	 * PrepareSearch
	 * Prepares search criteria string by
	 * utilizing the searcher object and
	 * building the string
	 *
	 * @param criteria array of criteria
	 * @return true if successful build
	 * @throws Exception
	 ***************************************/
	public function PrepareSearch(array $criteria = array()) {

		if(!isset($this->_searcher) || !($this->_searcher instanceof eSwipeImapSearchBuilder)) {
			$this->_searcher = new eSwipeImapSearchBuilder();
		}

		try {

			$this->_searcher->setCriteria($criteria);
			$this->_searcher->BuildCriteriaString();

		} catch(Exception $e) {
			throw $e;
		}

		return $this->_searcher->IsBuilt();

	}




	#endregion



}
#endregion

////////////////////////////////////////////////////////////////////
/////////////////         end          /////////////////////////////
////////////////////////////////////////////////////////////////////
?>

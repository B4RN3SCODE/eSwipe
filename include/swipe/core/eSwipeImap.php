<?php
#region dependencies
include_once("include/swipe/config/config.php");
include_once("eSwipeImapSearchBuilder.php");
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

	#endregion



}
#endregion

////////////////////////////////////////////////////////////////////
/////////////////         end          /////////////////////////////
////////////////////////////////////////////////////////////////////
?>

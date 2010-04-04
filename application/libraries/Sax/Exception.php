<?php
/**
 * SAXException Class
 *
 * This class defines the SAX parser exception.
 *
 * @access   public
 */

class Sax_Exception extends Exception {
	protected $lineNumber, $columnNumber;

  public function __construct($sax,$message = NULL, $code = 0) {
  	if ($code == 0) $code = xml_get_error_code($sax);
  	if ($message == NULL) $message = xml_error_string($code);
    parent::__construct($message, $code);
    $this->lineNumber = xml_get_current_line_number($sax);
    $this->columnNumber = xml_get_current_column_number($sax);
  }

  public function __toString() {
    return __CLASS__ . " >> [{$this->code}]:".
                       " {$this->message} at ".
                       "{$this->lineNumber},{$this->columnNumber}\n";
  }
}

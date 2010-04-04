<?php

/**
 * This file contains the code for managing SAX via basic PHP XML Parser functions.
 * (see http://www.php.net/manual/en/ref.xml.php)
 *
 * PHP version 5.1
 *
 * @category   XML for PHP
 * @package    Sax4PHP
 * @version	   0.3
 * @author     Emmanuel Desmontils <emmanuel.desmontils@univ-nantes.fr> Original Author
 * @license    GNU GPL
 * @copyright  (c) 2007 Emmanuel Desmontils
 * @link       http://sax4php.sourceforge.net/
 *
 *   This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

/**
 * SaxParser Class
 *
 * This class is the main class for parsing an XML string using a SAX Handler.
 *
 * basic usage:<code>
 * $xml = file_get_contents('myFile.xml');
 * $sax = new SaxParser(new mySaxHandler());
 * $sax->parse($xml);
 * </code>
 *
 * @access   public
 */

class Sax_Parser {
    private $sax;
    private $saxHandler;

    function __construct(Sax_DefaultHandler $saxHandler, $encoding = 'UTF-8') {
        $this->saxHandler = $saxHandler;
        $this->sax = xml_parser_create($encoding);
        xml_set_object($this->sax,$saxHandler);
        xml_parser_set_option($this->sax,XML_OPTION_CASE_FOLDING, FALSE);
        $this->setElementHandler('startElementHandler','endElementHandler');
        $this->setDefaultHandler('defaultHandler');
        $this->setCharacterHandler('characterHandler');
        $this->setPIHandler('piHandler');
    }
    function __destruct() {xml_parser_free($this->sax);}

    function setElementHandler($openElementHandler, $closeElementHandler) {
        xml_set_element_handler($this->sax,$openElementHandler,$closeElementHandler);}
    function setDefaultHandler($defaultHandler) {
        xml_set_default_handler($this->sax,$defaultHandler);}
    function setCharacterHandler($characterHandler){
        xml_set_character_data_handler ($this->sax, $characterHandler);}
    function setPIHandler($piHandler){
        xml_set_processing_instruction_handler ($this->sax, $piHandler);}

    function parse($xml) {
        $this->saxHandler->startDocumentHandler($this->sax);
        $error = ! xml_parse($this->sax,$xml,TRUE);
        if ($error) {
            throw new Sax_Exception($this->sax,xml_error_string(xml_get_error_code($this->sax)),
            xml_get_error_code($this->sax));
        }
        $this->saxHandler->endDocumentHandler($this->sax);
    }
}

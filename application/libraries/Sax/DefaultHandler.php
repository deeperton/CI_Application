<?php
/**
 * Sax_DefaultHandler Class
 *
 * This class is the default handlers for majors types of events.
 *
 * basic usage:<code>
 *	class CompteurFormations extends SaxHandler {
 *	  function startElement($name, $att) {echo "<start name='$name'/>\n";}
 *	  function endElement($name) {echo "<end name='$name'/>\n";}
 *	  function startDocument() {echo '<?xml version="1.0" encoding="ISO-8859-1"?><list>';}
 *	  function endDocument() {echo '</list>';}
 *	}
 * </code>
 *
 * @access   public
 */

class Sax_DefaultHandler {
    function __construct() {}
    function __destruct(){}

    //Basic PHP/SAX object to handle events
    final function startElementHandler($sax, $name, $att) {$this->startElement($name, $att);}
    final function endElementHandler($sax, $name) { $this->endElement($name);}
    final function piHandler($sax, $target, $content) { $this->processingInstruction($target, $content);}
    final function defaultHandler($sax, $data) { $this->node($data);}
    final function characterHandler($sax, $string) { $this->characters($string);}
    final function startDocumentHandler($sax) { $this->startDocument();}
    final function endDocumentHandler($sax) { $this->endDocument();}

    //Java like functions to manage SAX events

    //void startElement(String�uri,String�localName,String�qName,Attributes�atts) throws SAXException
    function startElement($name, $att) {}

    //void endElement(String�uri,String�localName,String�qName) throws SAXException
    function endElement($name) {}

    //void processingInstruction(String�target, String�data) throws SAXException
    function processingInstruction($target, $content) {}

    //Not a Java Method but usefull for SAX 2 :
    // * void comment(char[] ch, int start, int length) throws SAXException
    // * void startEntity(String name) throws SAXException ?
    // * void endEntity(String name) throws SAXException ?
    // * void startDTD(String name, String publicId, String systemId) throws SAXException ?
    // * void endDTD() throws SAXException ?
    function node($data) {}

    //void characters(char[]�ch,int�start,int�length) throws SAXException
    //void ignorableWhitespace(char[]�ch, int�start, int�length) throws SAXException
    //CDATA section (like in SAX 2):
    // * void startCDATA() throws SAXException
    // * void endCDATA() throws SAXException
    function characters($string) {}

    //void startDocument() throws SAXException
    function startDocument() {}

    //void endDocument() throws SAXException
    function endDocument(){}
}

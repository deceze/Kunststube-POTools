<?php

namespace Kunststube\POTools;


class Catalog implements \IteratorAggregate {
    
    protected $strings           = array(),
              $projectIdVersion  = 'PACKAGE VERSION',
              $reportMsgidBugsTo = null,
              $potCreationDate   = null,
              $poRevisionDate    = 'YEAR-MO-DA HO:MI+ZONE',
              $lastTranslator    = 'FULL NAME <EMAIL@ADDRESS>',
              $languageTeam      = 'LANGUAGE <EMAIL@ADDRESS>',
              $language          = null,
              $pluralForms       = null,
              $encoding          = 'UTF-8';
    
    /**
     * @param string $projectIdVersion Name and version of the project
     * @param string $language ISO 639/ISO 3166 language code,
     *      see http://www.gnu.org/software/gettext/manual/gettext.html#Language-Codes
     * @param string $languageTeam Name of translation team (typically full language name) and contact address,
     *      for example: "French <french-team@example.com>"
     * @param string $encoding Encoding of strings/source material
     */
    public function __construct($projectIdVersion = null, $language = null, $languageTeam = null, $encoding = 'UTF-8') {
        $this->projectIdVersion = $projectIdVersion;
        $this->language         = $language;
        $this->languageTeam     = $languageTeam;
        $this->encoding         = $encoding;
        $this->potCreationDate  = date('Y-m-d H:iO');
    }
    
    /**
     * @param string $projectIdVersion Name and version of the project
     */
    public function setProjectIdVersion($projectIdVersion) {
        $this->projectIdVersion = $projectIdVersion;
    }
    
    public function getProjectIdVersion() {
        return $this->projectIdVersion;
    }
    
    /**
     * @param string $address Address that translation teams can report problems with the source material to.
     */
    public function setMsgidBugReportingAddress($address) {
        $this->reportMsgidBugsTo = $address;
    }
    
    public function getMsgidBugReportingAddress() {
        return $this->reportMsgidBugsTo;
    }
    
    /**
     * @param string $date Date of creation of the POT file in "Y-m-d H:iO" format.
     */
    public function setPotCreationDate($date) {
        $this->potCreationDate = $date;
    }
    
    public function getPotCreationDate() {
        return $this->potCreationDate;
    }
    
    /**
     * @param string $date Date of last change to PO file in "Y-m-d H:iO" format.
     */
    public function setPoRevisionDate($date) {
        $this->poRevisionDate = $date;
    }
    
    public function getPoRevisionDate() {
        return $this->poRevisionDate;
    }
    
    /**
     * @param string $nameAndEmail Name and email of last translator, e.g. "John Doe <john@example.com>"
     */
    public function setLastTranslator($nameAndEmail) {
        $this->lastTranslator = $nameAndEmail;
    }
    
    public function getLastTranslator() {
        return $this->lastTranslator;
    }
    
    /**
     * @param string $languageTeamAndAddress Name of translation team and contact address.
     */
    public function setLanguageTeam($languageNameAndAddress) {
        $this->languageTeam = $languageNameAndAddress;
    }
    
    public function getLanguageTeam() {
        return $this->languageTeam;
    }
    
    /**
     * @param string $languageCode ISO 639/ISO 3166 language code
     */
    public function setLanguage($languageCode) {
        $this->language = $languageCode;
    }
    
    public function getLanguage() {
        return $this->language;
    }
    
    /**
     * @param string $pluralForms See http://translate.sourceforge.net/wiki/l10n/pluralforms
     */
    public function setPluralForms($pluralForms) {
        $this->pluralForms = $pluralForms;
    }
    
    public function getPluralForms() {
        return $this->pluralForms;
    }
    
    /**
     * @param string $encoding Encoding of strings/source material
     */
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }
    
    public function getEncoding() {
        return $this->encoding;
    }
    
    /**
     * Add POString instance to catalog. If identical string already exists,
     * reference and extracted comment attributes will be merged.
     */
    public function add(POString $string) {
        $id       = $string->getMsgctxt() . "\04" . $string->getMsgid();
        $category = $string->getCategoryText();
        $domain   = $string->getDomain();
        
        if (isset($this->strings[$category][$domain][$id])) {
            array_map(array($this->strings[$category][$domain][$id], 'addReference'), $string->getReferences());
            array_map(array($this->strings[$category][$domain][$id], 'addExtractedComment'), $string->getExtractedComments());
        } else {
            $this->strings[$category][$domain][$id] = $string;
        }
    }
    
    /**
     * Get a flat iterable list of all strings in the catalog.
     */
    public function getIterator() {
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'RecursiveArrayOnlyIterator.php';
        return new \RecursiveIteratorIterator(new RecursiveArrayOnlyIterator($this->strings));
    }
 
    /**
     * Write the catalog to a directory. Entries will be split into subdirectories and
     * files based on category and domain.
     * 
     * @param string $path Path to output directory. Requires permissions to create new directories and files within.
     * @param POWriterFactory $writerFactory Instance of factory to instantiate individual POWriter objects for each file.
     */
    public function writeToDirectory($path, POWriterFactory $writerFactory = null) {
        if (!$writerFactory) {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'POWriterFactory.php';
            $writerFactory = new POWriterFactory;
        }
        
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!file_exists($path) || !is_dir($path)) {
            throw new \InvalidArgumentException("Cannot access path $path");
        }
        
        foreach ($this->strings as $category => $domains) {
            if (!file_exists($path . $category)) {
                mkdir($path . $category);
            }
            
            foreach ($domains as $domain => $strings) {
                $file = fopen($path . $category . DIRECTORY_SEPARATOR . "$domain.pot", 'w');
                $writer = $writerFactory->construct($file, $this);
                
                foreach ($strings as $string) {
                    $writer->write($string);
                }
                
                fclose($file);
            }
        }
    }
    
}
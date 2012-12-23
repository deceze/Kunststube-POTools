<?php

namespace Kunststube\POTools;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'POWriterInterface.php';


class POWriter implements POWriterInterface {
    
    protected $stream;
    
    public function __construct($stream = STDOUT) {
        $this->stream = $stream;
    }
    
    public function write(POString $string) {
        $this->writeReference($string);
        $this->writeMsgctxt($string);
        $this->writeMsgid($string);
        $this->writeMsgstr($string);
        $this->writeLine(null);
    }
    
    public function writeCatalog(Catalog $catalog) {
        foreach ($catalog as $string) {
            $this->write($string);
        }
    }
    
    protected function writeReference(POString $string) {
        foreach ($string->getReferences() as $reference) {
            $this->writeLine(sprintf('#: %s', $reference));
        }
    }
    
    protected function writeMsgctxt(POString $string) {
        if ($msgctxt = $string->getMsgctxt()) {
            $this->writeLine(sprintf('msgctxt %s', $this->quote($msgctxt)));
        }
    }
    
    protected function writeMsgid(POString $string) {
        $this->writeLine(sprintf('msgid %s', $this->quote($string->getMsgid())));
        if ($plural = $string->getMsgidPlural()) {
            $this->writeLine(sprintf('msgid_plural %s', $this->quote($plural)));
        }
    }
    
    protected function writeMsgstr(POString $string) {
        $msgstr = $string->getMsgstr();
        if ($string->getMsgidPlural()) {
            for ($i = 0, $length = count($msgstr); $i < $length; $i++) {
                $this->writeLine(sprintf('msgstr[%d] %s', $i, $this->quote($msgstr[$i])));
            }
        } else if ($msgstr) {
            $this->writeLine(sprintf('msgstr %s', $this->quote($msgstr[0])));
        } else {
            $this->writeLine('msgstr ""');
        }
    }
    
    protected function writeLine($line) {
        fwrite($this->stream, $line . "\n");
    }
    
    protected function quote($string) {
        return sprintf('"%s"', addcslashes($string, '"'));
    }
    
}
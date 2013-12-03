<?php

namespace Kunststube\POTools;


class POWriter {
    
    protected $stream,
              $outputWritten = false;
    
    /**
     * @param resource $stream A stream pointer resource to which the PO data is written.
     *                         Defaults to stdout.
     * @throws \InvalidArgumentException
     */
    public function __construct($stream = STDOUT, Catalog $catalog = null) {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('$stream must be a file/stream pointer resource, got ' . gettype($stream));
        }
        if (!in_array(get_resource_type($stream), array('file', 'stream'))) {
            throw new \InvalidArgumentException('$stream must be a stream pointer resource, got ' . get_resource_type($stream));
        }
        $this->stream = $stream;
        
        if ($catalog) {
            $this->writeHeader($catalog);
        }
    }
    
    public function writeHeader(Catalog $catalog) {
        if ($this->outputWritten) {
            throw new \LogicException('Cannot write header, output already written');
        }
        $this->outputWritten = true;
        
        $this->writeLine('msgid ""');
        $this->writeLine('msgstr ""');
        $this->writeLine(sprintf('"Project-Id-Version: %s\n"',               $catalog->getProjectIdVersion()));
        $this->writeLine(sprintf('"Report-Msgid-Bugs-To: %s\n"',             $catalog->getMsgidBugReportingAddress()));
        $this->writeLine(sprintf('"POT-Creation-Date: %s\n"',                $catalog->getPotCreationDate()));
        $this->writeLine(sprintf('"PO-Revision-Date: %s\n"',                 $catalog->getPoRevisionDate()));
        $this->writeLine(sprintf('"Last-Translator: %s\n"',                  $catalog->getLastTranslator()));
        $this->writeLine(sprintf('"Language-Team: %s\n"',                    $catalog->getLanguageTeam()));
        $this->writeLine(sprintf('"Language: %s\n"',                         $catalog->getLanguage()));
        $this->writeLine('"MIME-Version: 1.0\n"');
        $this->writeLine(sprintf('"Content-Type: text/plain; charset=%s\n"', $catalog->getEncoding()));
        $this->writeLine('"Content-Transfer-Encoding: 8bit\n"');
        
        if ($pluralForms = $catalog->getPluralForms()) {
            $this->writeLine(sprintf('"Plural-Forms: %s\n"', $pluralForms));
        }
        
        $this->writeLine(null);
    }
    
    /**
     * Write the complete entry for one POString to the stream.
     * 
     * @param POString $string
     */
    public function write(POString $string) {
        $this->writeTranslatorComment($string);
        $this->writeExtractedComments($string);
        $this->writeReference($string);
        $this->writeFlags($string);
        $this->writePreviousMsgctxt($string);
        $this->writePreviousMsgid($string);
        $this->writeMsgctxt($string);
        $this->writeMsgid($string);
        $this->writeMsgstr($string);
        $this->writeLine(null);
    }
    
    protected function writeTranslatorComment(POString $string) {
        if ($comments = $string->getTranslatorComment()) {
            foreach (explode("\n", $comments) as $commentLine) {
                $this->writeLine(sprintf('# %s', $commentLine));
            }
        }
    }
    
    protected function writeExtractedComments(POString $string) {
        foreach ($string->getExtractedComments() as $comment) {
            foreach (explode("\n", $comment) as $commentLine) {
                $this->writeLine(sprintf('#. %s', $commentLine));
            }
        }
    }
    
    protected function writeReference(POString $string) {
        foreach ($string->getReferences() as $reference) {
            $this->writeLine(sprintf('#: %s', $reference));
        }
    }
    
    protected function writeFlags(POString $string) {
        if ($flags = $string->getFlags()) {
            $this->writeLine(sprintf('#, %s', implode(', ', $flags)));
        }
    }
    
    protected function writePreviousMsgctxt(POString $string) {
        if ($msgctxt = $string->getPreviousMsgctxt()) {
            $this->writeLine(sprintf('#| msgctxt %s', $msgctxt));
        }
    }

    protected function writePreviousMsgid(POString $string) {
        if ($msgid = $string->getPreviousMsgid()) {
            $this->writeLine(sprintf('#| msgid %s', $msgid));
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
        if ($string->getMsgidPlural() && $msgstr) {
            for ($i = 0, $length = count($msgstr); $i < $length; $i++) {
                $this->writeLine(sprintf('msgstr[%d] %s', $i, $this->quote($msgstr[$i])));
            }
        } else if ($string->getMsgidPlural()) {
            $this->writeLine('msgstr[0] ""');
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
        $string = addcslashes($string, '"');
        // replace line breaks with a literal \n, closing quotes, a line break and opening quotes
        $string = str_replace("\n", "\\n\"\n\"", $string);
        return sprintf('"%s"', $string);
    }
    
}
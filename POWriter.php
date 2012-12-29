<?php

namespace Kunststube\POTools;


class POWriter {
    
    protected $stream;
    
    /**
     * @param resource $stream A stream pointer resource to which the PO data is written.
     *                         Defaults to stdout.
     * @throws \InvalidArgumentException
     */
    public function __construct($stream = STDOUT) {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('$stream must be a file/stream pointer resource, got ' . gettype($stream));
        }
        if (!in_array(get_resource_type($stream), array('file', 'stream'))) {
            throw new \InvalidArgumentException('$stream must be a stream pointer resource, got ' . get_resource_type($stream));
        }
        $this->stream = $stream;
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
        return sprintf('"%s"', addcslashes($string, '"'));
    }
    
}
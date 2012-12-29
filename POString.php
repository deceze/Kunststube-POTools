<?php

namespace Kunststube\POTools;


class POString {
    
    protected $category          = LC_MESSAGES,
              $domain            = 'default',
              $msgid             = null,
              $msgidPlural       = null,
              $msgstr            = array(),
              $msgctxt           = null,
              $translatorComment = null,
              $extractedComments = array(),
              $references        = array(),
              $flags             = array(),
              $previousMsgctxt   = null,
              $previousMsgid     = null;
    
    public function __construct($msgid, $msgidPlural = null) {
        $this->setMsgid($msgid);
        $this->setMsgidPlural($msgidPlural);
    }

    public function setCategory($category) {
        try {
            $this->categoryToText($category);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Invalid category '$category'", 0, $e);
        }
        $this->category = $category;
    }
    
    public function getCategory() {
        return $this->category;
    }
    
    public function getCategoryText() {
        return $this->categoryToText($this->category);
    }
    
    protected function categoryToText($category) {
        switch ($category) {
            case LC_MESSAGES :
                return 'LC_MESSAGES';
            case LC_CTYPE :
                return 'LC_CTYPE';
            case LC_NUMERIC :
                return 'LC_NUMERIC';
            case LC_TIME :
                return 'LC_TIME';
            case LC_COLLATE :
                return 'LC_COLLATE';
            case LC_MONETARY :
                return 'LC_MONETARY';
            case LC_ALL :
                return 'LC_ALL';
            default :
                throw new \InvalidArgumentException("Invalid category '$category'");
        }
    }
    
    public function setDomain($domain) {
        $this->domain = $domain;
    }
    
    public function getDomain() {
        return $this->domain;
    }
    
    public function setMsgid($msgid) {
        $this->msgid = $msgid;
    }
    
    public function getMsgid() {
        return $this->msgid;
    }
    
    public function setMsgidPlural($msgidPlural) {
        $this->msgidPlural = $msgidPlural;
    }
    
    public function getMsgidPlural() {
        return $this->msgidPlural;
    }

    public function addMsgstr($msgstr) {
        $this->msgstr[] = $msgstr;
    }
    
    public function setMsgstr($msgstr) {
        $this->msgstr = array_values((array)$msgstr);
    }
    
    public function getMsgstr() {
        return $this->msgstr;
    }
    
    public function setMsgctxt($msgctxt) {
        $this->msgctxt = $msgctxt;
    }
    
    public function getMsgctxt() {
        return $this->msgctxt;
    }
    
    public function setTranslatorComment($comment) {
        $this->translatorComment = $comment;
    }
    
    public function getTranslatorComment() {
        return $this->translatorComment;
    }
    
    public function addExtractedComment($comment) {
        $this->extractedComments[] = $comment;
    }
    
    public function setExtractedComments(array $comments) {
        $this->extractedComments = $comments;
    }
    
    public function getExtractedComments() {
        return $this->extractedComments;
    }
    
    public function addReference($reference) {
        $this->references[] = $reference;
    }
    
    public function setReferences(array $references) {
        $this->references = $references;
    }
    
    public function getReferences() {
        return $this->references;
    }
    
    public function addFlag($flag) {
        $flag = strtolower($flag);
        if (!in_array($flag, $this->flags)) {
            $this->flags[] = $flag;
        }
    }
    
    public function setFlags(array $flags) {
        $this->flags = $flags;
    }
    
    public function getFlags() {
        return $this->flags;
    }

    public function hasFlag($flag) {
        return in_array(strtolower($flag), $this->flags);
    }
    
    public function isFuzzy() {
        return $this->hasFlag('fuzzy');
    }
    
    public function setPreviousMsgctxt($msgctxt) {
        $this->previousMsgctxt = $msgctxt;
    }
    
    public function getPreviousMsgctxt() {
        return $this->previousMsgctxt;
    }
    
    public function setPreviousMsgid($msgid) {
        $this->previousMsgid = $msgid;
    }
    
    public function getPreviousMsgid() {
        return $this->previousMsgid;
    }
    
}
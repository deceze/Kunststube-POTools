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
    
    /**
     * Add a comment automatically extracted from source code.
     * If the comment contains an xgettext format flag on a single
     * line, the flag will automatically be added (see
     * http://www.gnu.org/software/gettext/manual/gettext.html#c_002dformat-Flag).
     * 
     * @param string $comment
     */
    public function addExtractedComment($comment) {
        $this->extractedComments[] = $comment;
        $this->processCommentFlags($comment);
    }
    
    /**
     * Replaces all extracted comments with a new set of comments.
     * 
     * @param array $comments An array of strings.
     */
    public function setExtractedComments(array $comments) {
        $this->extractedComments = array();
        array_map(array($this, 'addExtractedComment'), $comments);
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
    
    /**
     * Add a flag. Format flags are binary, a "no-php-format"
     * flag will replace a "php-format" flag and vice versa.
     * 
     * @param string $flag One of "no-*-format", "*-format", "fuzzy", "range: n..m"
     */
    public function addFlag($flag) {
        $flag = strtolower($flag);
        if (preg_match('/^(?:no-)?(\w+-format)$/', $flag, $match)) {
            $this->removeFlag($match[0]);
            $this->removeFlag($match[1]);
        }
        if (!in_array($flag, $this->flags)) {
            $this->flags[] = $flag;
        }
    }
    
    public function setFlags(array $flags) {
        $this->flags = array();
        array_map(array($this, 'addFlag'), $flags);
    }
    
    public function removeFlag($flag) {
        $index = array_search($flag, $this->flags, true);
        if ($index !== false) {
            $this->flags = array_splice($this->flags, $index, 1);
        }
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
    
    protected function processCommentFlags($comment) {
        if (preg_match('/^\s*xgettext:((?:no-)?\w+-format)\s*$/m', $comment, $match)) {
            $this->addFlag($match[1]);
        }
    }
    
}
<?php
/**
 * "Unified" diff renderer.
 *
 * This class renders the diff in classic "unified diff" format.
 *
 * $Horde: framework/Text_Diff/Diff/Renderer/unified.php,v 1.2 2004/01/09 21:46:30 chuck Exp $
 *
 * @package Horde_Text_Diff
 */
require_once 'Horde/Text/Diff/Renderer.php';
class Horde_Text_Diff_Renderer_pearweb extends Horde_Text_Diff_Renderer {

    /**
     * Number of leading context "lines" to preserve.
     */
    var $_leading_context_lines = 4;

    /**
     * Number of trailing context "lines" to preserve.
     */
    var $_trailing_context_lines = 4;

    protected function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        $removed = $xlen - $ylen;
        if ($removed > 0) {
            return '<span class="diffheader">Line ' . $xbeg . ' (now ' . $ybeg . '), was ' .
                $xlen . ' lines, now ' . $ylen . ' lines</span>';
        }
    }

    protected function _added($lines)
    {
        array_walk(
            $lines,
            function (&$a, $b) {
                $a = htmlspecialchars($a);
            }
        );
        return '<span class="newdiff"> ' . implode("</span>\n<span class='newdiff'> ", $lines) .
            '</span>';
    }

    protected function _context($lines)
    {
        array_walk(
            $lines,
            function (&$a, $b) {
                $a = htmlspecialchars($a);
            }
        );
        return "\n" . parent::_context($lines);
    }

    protected function _deleted($lines)
    {
        array_walk(
            $lines,
            function (&$a, $b) {
                $a = htmlspecialchars($a);
            }
        );
        return '<span class="olddiff"> ' . implode("</span>\n<span class='olddiff'> ", $lines) .
            '</span>';
    }

    protected function _changed($orig, $final)
    {
        return $this->_deleted($orig) . "\n" . $this->_added($final);
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
  function removeSimpleXmlNode($node)
  {
  $dom = dom_import_simplexml($node);
  $dom->parentNode->removeChild($dom);
  }

  function replaceSimpleXmlNode($xml, SimpleXMLElement $element) {
  $dom     = dom_import_simplexml($xml);
  $import  = $dom->ownerDocument->importNode(
  dom_import_simplexml($element),TRUE
  );
  $dom->parentNode->replaceChild($import, $dom);
  }

 */
/*
function after($thing, $inthat)
{
    if (!is_bool(strpos($inthat, $thing))) {
        return substr($inthat, strpos($inthat, $thing) + strlen($thing));
    }
}

function after_last($thing, $inthat)
{
    if (!is_bool(strrevpos($inthat, $thing))) {
        return substr($inthat, strrevpos($inthat, $thing) + strlen($thing));
    }
}
function before($thing, $inthat)
{
    return substr($inthat, 0, strpos($inthat, $thing));
}

function before_last($thing, $inthat)
{
    return substr($inthat, 0, strrevpos($inthat, $thing));
}

function between($thing, $that, $inthat)
{
    return before($that, after($thing, $inthat));
}

function between_last($thing, $that, $inthat)
{
    return after_last($thing, before_last($that, $inthat));
}

function strrevpos($instr, $needle)
{
    $rev_pos = strpos(strrev($instr), strrev($needle));
    if ($rev_pos === false) {
        return false;
    } else {
        return strlen($instr) - $rev_pos - strlen($needle);
    }
}

function strpos_array($haystack, $needles)
{
    if (is_array($needles)) {
        foreach ($needles as $str) {
            if (is_array($str)) {
                $pos = strpos_array($haystack, $str);
            } else {
                $pos = strpos($haystack, $str);
            }
            if ($pos !== false) {
                return $pos;
            }
        }
    } else {
        return strpos($haystack, $needles);
    }
    return false;
}
 *
 *
*/

#!/usr/bin/php
<?php

  function color($code) {
    return "\033[{$code}m";
  }

  function getStdInput() {
    $input = array();
    while($f = fgets(STDIN)){
        $input[] = $f;
    }
    $input = implode("\n", $input);
    return $input;
  }

  function addDateLineBreaks($string) {
    $input = preg_replace("/(\[[A-Z][a-z][a-z] .*\[client [0-9:.]+\] )/", "\n" . '\1' . "\n", $string);
    return $input;
  }

  function addRefererLineBreaks($string) {
    $string = preg_replace('/, (referer:.*\n)/', "\n" . '\1', $string);
    return $string;
  }

  function addLineBreaks($string) {
    $input = preg_replace('/(#[0-9][0-9]?[0-9]? )/', "\n" . '\1', $string);
    $input = preg_replace('/(#[0-9][0-9]?[0-9]? [^:]*:)/', '\1' . "\n", $input);
    $input = str_replace('\n', '', $input);
    return $input;
  }

  function colorizeAndIndent($string) {

    $C_WHITE = color(0);
    $C_BLUE = color(34);
    $C_GREEN = color(36);
    $C_YELLOW = color(33);
    $SPACE = "    ";

    $input = preg_replace('/\n(\[[A-Z][a-z][a-z] .*\[client [0-9:.]+\])/', "\n\n$C_WHITE-----\n\n$C_BLUE" . '\1' . "$C_YELLOW", $string);
    $input = preg_replace('/\n(#[0-9]+ )/', "\n" . "$C_GREEN$SPACE" . '\1', $input);
    $input = preg_replace('/\n(referer:.*)/', "\n$C_BLUE" . '\1', $input);
    $input = str_replace('\n', '', $input);

    return $input;
  }

  function removeLinesBeforeStackTrace($string) {
    $oldArray = explode("\n", $string);
    $newArray = array();

    for ($i=0; $i<count($oldArray)-1; $i++) {
      $removableRegex = array(
        '/^\[[A-Za-z]{3}/',
        '/^referer:/',
      );
      $removable = false;
      foreach ($removableRegex as $check) {
        if (preg_match($check, $oldArray[$i])) {
          $removable = true;
        }
      }
      if ($removable) {
        $hasWord = false;
        $words = array(
          '/PHP Stack trace:/',
          '/^PHP\s+[0-9]+\./'
        );
        foreach ($words as $regex) {
          if (preg_match($regex, $oldArray[$i+1])) {
            $hasWord = true;
          }
        }
      }
      if (!$removable or !$hasWord) {
        $newArray[] = $oldArray[$i];
      }
    }
    return implode($newArray, "\n") . "\n";
  }

  function removeDuplicateLineBreaks($string) {
    $string = preg_replace('/\n{2,}/', "\n", $string);
    $string = preg_replace('/\n\s+/', "\n", $string);
    return $string;
  }

  function replacePhpNumber($string) {
    $string = preg_replace('/PHP\s+([0-9]+)./', '#\1', $string);
    return $string;
  }

  function addStackStepLineBreaks($string) {
    $string = preg_replace('/- #0/', "\n#0", $string);
    $string = preg_replace('/.n(#[0-9]+)/', "\n" . '\1', $string);
    return $string;
  }

  $content = getStdInput();
  $content = addDateLineBreaks($content);
  $content = addRefererLineBreaks($content);
  $content = removeDuplicateLineBreaks($content);
  $content = removeLinesBeforeStackTrace($content);
  $content = removeLinesBeforeStackTrace($content);
  $content = addStackStepLineBreaks($content);
  $content = replacePhpNumber($content);
  $content = removeDuplicateLineBreaks($content);
  $content = colorizeAndIndent($content);
  echo $content;

?>

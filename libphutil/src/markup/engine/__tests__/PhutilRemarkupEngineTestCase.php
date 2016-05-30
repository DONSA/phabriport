<?php

/**
 * Test cases for @{class:PhutilRemarkupEngine}.
 */
final class PhutilRemarkupEngineTestCase extends PhutilTestCase {

  public function testEngine() {
    $root = dirname(__FILE__).'/remarkup/';
    foreach (Filesystem::listDirectory($root, $hidden = false) as $file) {
      $this->markupText($root.$file);
    }
  }

  private function markupText($markup_file) {
    $contents = Filesystem::readFile($markup_file);
    $file = basename($markup_file);

    $parts = explode("\n~~~~~~~~~~\n", $contents);
    $this->assertEqual(3, count($parts), $markup_file);

    list($input_remarkup, $expected_output, $expected_text) = $parts;

    $engine = $this->buildNewTestEngine();

    switch ($file) {
      case 'raw-escape.txt':

        // NOTE: Here, we want to test PhutilRemarkupEscapeRemarkupRule and
        // PhutilRemarkupBlockStorage, which are triggered by "\1". In the
        // test, "~" is used as a placeholder for "\1" since it's hard to type
        // "\1".

        $input_remarkup = str_replace('~', "\1", $input_remarkup);
        $expected_output = str_replace('~', "\1", $expected_output);
        $expected_text = str_replace('~', "\1", $expected_text);
        break;
      case 'toc.txt':
        $engine->setConfig('header.generate-toc', true);
        break;
    }

    $actual_output = (string)$engine->markupText($input_remarkup);

    switch ($file) {
      case 'toc.txt':
        $table_of_contents =
          PhutilRemarkupHeaderBlockRule::renderTableOfContents($engine);
        $actual_output = $table_of_contents."\n\n".$actual_output;
        break;
    }

    $this->assertEqual(
      $expected_output,
      $actual_output,
      pht("Failed to markup HTML in file '%s'.", $file));

    $engine->setMode(PhutilRemarkupEngine::MODE_TEXT);
    $actual_output = (string)$engine->markupText($input_remarkup);

    $this->assertEqual(
      $expected_text,
      $actual_output,
      pht("Failed to markup text in file '%s'.", $file));
  }

  private function buildNewTestEngine() {
    $engine = new PhutilRemarkupEngine();
    $engine->setConfig('uri.prefix', 'http://www.example.com/');

    $engine->setConfig(
      'uri.allowed-protocols',
      array(
        'http' => true,
        'mailto' => true,
        'tel' => true,
      ));

    $rules = array();
    $rules[] = new PhutilRemarkupEscapeRemarkupRule();
    $rules[] = new PhutilRemarkupMonospaceRule();
    $rules[] = new PhutilRemarkupDocumentLinkRule();
    $rules[] = new PhutilRemarkupHyperlinkRule();
    $rules[] = new PhutilRemarkupBoldRule();
    $rules[] = new PhutilRemarkupItalicRule();
    $rules[] = new PhutilRemarkupDelRule();
    $rules[] = new PhutilRemarkupUnderlineRule();
    $rules[] = new PhutilRemarkupHighlightRule();

    $blocks = array();
    $blocks[] = new PhutilRemarkupQuotesBlockRule();
    $blocks[] = new PhutilRemarkupReplyBlockRule();
    $blocks[] = new PhutilRemarkupHeaderBlockRule();
    $blocks[] = new PhutilRemarkupHorizontalRuleBlockRule();
    $blocks[] = new PhutilRemarkupCodeBlockRule();
    $blocks[] = new PhutilRemarkupLiteralBlockRule();
    $blocks[] = new PhutilRemarkupNoteBlockRule();
    $blocks[] = new PhutilRemarkupTableBlockRule();
    $blocks[] = new PhutilRemarkupSimpleTableBlockRule();
    $blocks[] = new PhutilRemarkupDefaultBlockRule();
    $blocks[] = new PhutilRemarkupListBlockRule();
    $blocks[] = new PhutilRemarkupInterpreterBlockRule();

    foreach ($blocks as $block) {
      if (!($block instanceof PhutilRemarkupCodeBlockRule)) {
        $block->setMarkupRules($rules);
      }
    }

    $engine->setBlockRules($blocks);

    return $engine;
  }

}

<?php
/**
 * Utility to convert binary ESC/POS data to HTML
 */
require_once __DIR__ . '/vendor/autoload.php';

use ReceiptPrintHq\EscposTools\Parser\Parser;
use ReceiptPrintHq\EscposTools\Parser\Context\InlineFormatting;

// Usage
if (!isset($argv[1])) {
    print "Usage: " . $argv[0] . " filename\n";
    die();
}


// Load in a file
$fp = fopen($argv[1], 'rb');

$parser = new Parser();
$parser -> addFile($fp);

// Extract text
$commands = $parser -> getCommands();
$formatting = InlineFormatting::getDefault();
$outp = array();
$lineHtml = "";
$bufferedImg = null;
$imgNo = 0;
$skipLineBreak = false;
foreach ($commands as $cmd) {
    if ($cmd -> isAvailableAs('InitializeCmd')) {
        $formatting = InlineFormatting::getDefault();
    }
    if ($cmd -> isAvailableAs('InlineFormattingCmd')) {
        $cmd -> applyToInlineFormatting($formatting);
    }
    if ($cmd -> isAvailableAs('TextContainer')) {
    // Add text to line
    // Decode text from legacy code page to UTF-8 if necessary
        $spanContentText = $cmd -> getText();
        if (!mb_check_encoding($spanContentText, 'UTF-8')) {
            $spanContentText = iconv('CP437', 'UTF-8//IGNORE', $spanContentText);
        }
        $lineHtml .= span($formatting, $spanContentText);
    }
    if ($cmd -> isAvailableAs('LineBreak') && $skipLineBreak) {
        $skipLineBreak = false;
    } elseif ($cmd -> isAvailableAs('LineBreak')) {
        // Write fresh block element out to HTML
        if ($lineHtml === "") {
            $lineHtml = span($formatting);
        }
        // Block-level formatting such as text justification
        $classes = getBlockClasses($formatting);
        $classesStr = implode(" ", $classes);
        $outp[] = wrapInline("<div class=\"$classesStr\">", "</div>", $lineHtml);
        $lineHtml = "";
    }
    if ($cmd -> isAvailableAs('GraphicsDataCmd') || $cmd -> isAvailableAs('GraphicsLargeDataCmd')) {
        $sub = $cmd -> subCommand();
        if ($sub -> isAvailableAs('StoreRasterFmtDataToPrintBufferGraphicsSubCmd')) {
            $bufferedImg = $sub;
        } elseif ($sub -> isAvailableAs('PrintBufferredDataGraphicsSubCmd') && $bufferedImg !== null) {
            // Append and flush buffer
            $classes = getBlockClasses($formatting);
            $classesStr = implode(" ", $classes);
            $outp[] = wrapInline("<div class=\"$classesStr\">", "</div>", imgAsDataUrl($bufferedImg));
            $lineHtml = "";
        }
    } elseif ($cmd -> isAvailableAs('ImageContainer')) {
        // Append and flush buffer
        $classes = getBlockClasses($formatting);
        $classesStr = implode(" ", $classes);
        $outp[] = wrapInline("<div class=\"$classesStr\">", "</div>", imgAsDataUrl($cmd));
        $lineHtml = "";
        // Should load into print buffer and print next line break, but we print immediately, so need to skip the next line break.
        $skipLineBreak = true;
    }
}

// Stuff we need in the HTML header
const CSS_FILE = __DIR__ . "/src/resources/esc2html.css";
$metaInfo = array_merge(
    array(
        "<meta charset=\"UTF-8\">",
        "<style>"
    ),
    explode("\n", trim(file_get_contents(CSS_FILE))),
    array(
        "</style>"
    )
);

// Final document assembly
$receipt = wrapBlock("<div class=\"esc-receipt\">", "</div>", $outp);
$head = wrapBlock("<head>", "</head>", $metaInfo);
$body = wrapBlock("<body>", "</body>", $receipt);
$html = wrapBlock("<html>", "</html>", array_merge($head, $body), false);
echo "<!DOCTYPE html>\n" . implode("\n", $html) . "\n";

function imgAsDataUrl($bufferedImg)
{
    $imgAlt = "Image " . $bufferedImg -> getWidth() . 'x' . $bufferedImg -> getHeight();
    $imgSrc = "data:image/png;base64," . base64_encode($bufferedImg -> asPng());
    $imgWidth = $bufferedImg -> getWidth() / 2; // scaling, images are quite high res and dwarf the text
    $bufferedImg = null;
    return "<img class=\"esc-bitimage\" src=\"$imgSrc\" alt=\"$imgAlt\" width=\"{$imgWidth}px\" />";
}

function wrapInline($tag, $closeTag, $content)
{
    return $tag . $content . $closeTag;
}

function wrapBlock($tag, $closeTag, array $content, $indent = true)
{
    $ret = array();
    $ret[] = $tag;
    foreach ($content as $line) {
        $ret[] = ($indent ? '  ' : '') . $line;
    }
    $ret[] = $closeTag;
    return $ret;
}


function getSpanClasses(InlineFormatting $formatting)
{
    if ($formatting->widthMultiple > 8) {
        $formatting->widthMultiple = 8;
    }
    if ($formatting->heightMultiple > 8) {
        $formatting->heightMultiple = 8;
    }
    $classes = array();
    if ($formatting->bold) {
        $classes[] = "esc-emphasis";
    }
    if ($formatting->underline > 0) {
        $classes[] = $formatting->underline > 1 ? "esc-underline-double" : "esc-underline";
    }
    if ($formatting->invert) {
        $classes[] = "esc-invert";
    }
    if ($formatting->upsideDown) {
        $classes[] = "esc-upside-down";
    }
    if ($formatting->font == 1) {
        $classes[] = "esc-font-b";
    }
    if ($formatting->widthMultiple > 1 || $formatting->heightMultiple > 1) {
        $classes[] = "esc-text-scaled";
        $widthClass = $formatting->widthMultiple > 1 ? "-width-" . $formatting->widthMultiple : "";
        $heightClass = $formatting->heightMultiple > 1 ? "-height-" . $formatting->heightMultiple : "";
        $classes[] = "esc" . $widthClass . $heightClass;
    }
    return $classes;
}

function span(InlineFormatting $formatting, $spanContentText = false)
{
    $classes = getSpanClasses($formatting);
    $spanContentHtml = ($spanContentText === false) ? "&nbsp;" : htmlentities($spanContentText);
    if (empty($classes)) {
        return $spanContentHtml;
    } else {
        return "<span class=\"" . implode(" ", $classes) . "\">" . $spanContentHtml . "</span>";
    }
}

function getBlockClasses($formatting)
{
    $classes = ["esc-line"];
    if ($formatting -> justification === InlineFormatting::JUSTIFY_CENTER) {
        $classes[] = "esc-justify-center";
    } elseif ($formatting -> justification === InlineFormatting::JUSTIFY_RIGHT) {
        $classes[] = "esc-justify-right";
    }
    return $classes;
}

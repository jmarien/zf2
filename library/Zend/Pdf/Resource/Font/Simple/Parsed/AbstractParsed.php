<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Fonts
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Zend\Pdf\Resource\Font\Simple\Parsed;
use Zend\Pdf\InternalType;
use Zend\Pdf\BinaryParser\Font\OpenType as OpenTypeFontParser;
use Zend\Pdf;

/**
 * Parsed and (optionaly) embedded fonts implementation
 *
 * OpenType fonts can contain either TrueType or PostScript Type 1 outlines.
 *
 * @package    Zend_PDF
 * @subpackage Zend_PDF_Fonts
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class AbstractParsed extends \Zend\Pdf\Resource\Font\Simple\AbstractSimple
{
    /**
     * Object constructor
     *
     * @param \Zend\Pdf\BinaryParser\Font\OpenType\AbstractOpenType $fontParser Font parser object containing OpenType file.
     * @throws \Zend\Pdf\Exception\ExceptionInterface
     */
    public function __construct(OpenTypeFontParser\AbstractOpenType $fontParser)
    {
        parent::__construct();


        $fontParser->parse();

        /* Object properties */

        $this->_fontNames = $fontParser->names;

        $this->_isBold       = $fontParser->isBold;
        $this->_isItalic     = $fontParser->isItalic;
        $this->_isMonospaced = $fontParser->isMonospaced;

        $this->_underlinePosition  = $fontParser->underlinePosition;
        $this->_underlineThickness = $fontParser->underlineThickness;
        $this->_strikePosition     = $fontParser->strikePosition;
        $this->_strikeThickness    = $fontParser->strikeThickness;

        $this->_unitsPerEm = $fontParser->unitsPerEm;

        $this->_ascent  = $fontParser->ascent;
        $this->_descent = $fontParser->descent;
        $this->_lineGap = $fontParser->lineGap;

        $this->_glyphWidths       = $fontParser->glyphWidths;
        $this->_missingGlyphWidth = $this->_glyphWidths[0];


        $this->_cmap = $fontParser->cmap;


        /* Resource dictionary */

        $baseFont = $this->getFontName(Pdf\Font::NAME_POSTSCRIPT, 'en', 'UTF-8');
        $this->_resource->BaseFont = new InternalType\NameObject($baseFont);

        $this->_resource->FirstChar = new InternalType\NumericObject(0);
        $this->_resource->LastChar  = new InternalType\NumericObject(count($this->_glyphWidths) - 1);

        /* Now convert the scalar glyph widths to \Zend\Pdf\InternalType\NumericObect objects.
         */
        $pdfWidths = array();
        foreach ($this->_glyphWidths as $width) {
            $pdfWidths[] = new InternalType\NumericObject($this->toEmSpace($width));
        }
        /* Create the \Zend\Pdf\InternalType\ArrayObject object and add it to the font's
         * object factory and resource dictionary.
         */
        $widthsArrayElement = new InternalType\ArrayObject($pdfWidths);
        $widthsObject = $this->_objectFactory->newObject($widthsArrayElement);
        $this->_resource->Widths = $widthsObject;
    }
}

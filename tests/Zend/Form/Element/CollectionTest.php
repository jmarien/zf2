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
 * @package    Zend_Form
 * @subpackage UnitTest
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Form\Element;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Form\Element\Collection as Collection;
use Zend\Form\Factory;

class CollectionTest extends TestCase
{
    protected $form;

    public function setUp()
    {
        $this->form = new \ZendTest\Form\TestAsset\FormCollection();

        parent::setUp();
    }

    public function testCanRetrieveDefaultPlaceholder()
    {
        $placeholder = $this->form->get('colors')->getTemplatePlaceholder();
        $this->assertEquals('__index__', $placeholder);
    }

    public function testGenerateEmptySpecificationWhenTemplateIsNotWanted()
    {
        $spec = $this->form->get('colors')->getInputFilterSpecification();
        $this->assertEquals(array(), $spec);
    }

    public function testGenerateSpecificationWhenTemplateIsWanted()
    {
        $collection = $this->form->get('colors');
        $collection->setShouldCreateTemplate(true);
        $spec = $collection->getInputFilterSpecification();

        $expectedSpec = array(
            '__index__' => array(
                'required' => false
            )
        );

        $this->assertEquals($expectedSpec, $spec);

        $collection->setTemplatePlaceholder('__template__');
        $spec = $collection->getInputFilterSpecification();

        $expectedSpec = array(
            '__template__' => array(
                'required' => false
            )
        );

        $this->assertEquals($expectedSpec, $spec);
    }

    public function testCannotAllowNewElementsIfAllowAddIsFalse()
    {
        $collection = $this->form->get('colors');
        $collection->setAllowAdd(false);

        // By default, $collection contains 2 elements
        $data = array();
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        $this->assertEquals(2, count($collection->getElements()));

        $data[] = 'orange';
        $collection->populateValues($data);
        $this->assertEquals(2, count($collection->getElements()));
    }

    public function testCanAddNewElementsIfAllowAddIsTrue()
    {
        $collection = $this->form->get('colors');
        $collection->setAllowAdd(true);

        // By default, $collection contains 2 elements
        $data = array();
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        $this->assertEquals(2, count($collection->getElements()));

        $data[] = 'orange';
        $collection->populateValues($data);
        $this->assertEquals(3, count($collection->getElements()));
    }
}

<?php
namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use ArgumentCountError;
use Sortie\Sortie;

class ConstructTest extends AbstractTestCase
{
  /**
   * testComplexField
   */
  #[Group("construct")]
  public function testComplexField()
  {
    $expectedField = '[foo->one|bar->one:param|baz] - [qux->one->two:param:param]';

    $expectedExpressions = [
      [
        'expression' => 'foo->one|bar->one:param|baz',
        'options' => [
          [
            'property'  => 'foo',
            'modifiers' => ['one']
          ],
          [
            'property'  => 'bar',
            'modifiers' => ['one:param']
          ],
          [
            'property'  => 'baz',
            'modifiers' => []
          ]
        ],
        'type' => 'simple',
      ],
      [
        'expression' => 'qux->one->two:param:param',
        'options' => [
          [
            'property'  => 'qux',
            'modifiers' => ['one', 'two:param:param']
          ]
        ],
        'type' => 'simple',
      ]
    ];

    $expectedProperties = ['foo', 'bar', 'baz', 'qux'];

    $sortie = new Sortie($expectedField);

    $this->assertSortie($sortie);

    $this->assertSame($expectedExpressions, $sortie->getExpressions());
    $this->assertSame($expectedField, $sortie->getField());
    $this->assertSame($expectedProperties, $sortie->getProperties());
  }

  /**
   * testEmptyField
   */
  #[Group("construct")]
  public function testEmptyField()
  {
    $sortie = new Sortie('');

    $this->assertSortie($sortie);

    $this->assertEmpty($sortie->getExpressions());
    $this->assertEmpty($sortie->getField());
    $this->assertEmpty($sortie->getProperties());
  }

  /**
   * testIntegerField
   */
  #[Group("construct")]
  public function testIntegerField()
  {
    $sortie = new Sortie(123);

    $this->assertSortie($sortie);

    $this->assertEmpty($sortie->getExpressions());
    $this->assertEmpty($sortie->getField());
    $this->assertEmpty($sortie->getProperties());
  }

  /**
   * testNullField
   */
  #[Group("construct")]
  public function testNullField()
  {
    $this->expectException(ArgumentCountError::class);

    $sortie = new Sortie();
  }

  /**
   * testSimpleField
   */
  #[Group("construct")]
  public function testSimpleField()
  {
    $expectedField = '[foo]';

    $expectedExpressions = [
      [
        'expression' => 'foo',
        'options' => [
          [
            'property'  => 'foo',
            'modifiers' => []
          ]
        ],
        'type' => 'simple',
      ]
    ];

    $expectedProperties = ['foo'];

    $sortie = new Sortie($expectedField);

    $this->assertSortie($sortie);

    $this->assertSame($expectedExpressions, $sortie->getExpressions());
    $this->assertSame($expectedField, $sortie->getField());
    $this->assertSame($expectedProperties, $sortie->getProperties());
  }
}

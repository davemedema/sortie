<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifyLimitTest extends AbstractTestCase
{
  /**
   * @const string
   */
  const TEST_INPUT = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';

  // Data Providers
  // ---------------------------------------------------------------------------

  /**
   * dataNoParams
   */
  public static function dataNoParams()
  {
    return [
      [self::TEST_INPUT, self::TEST_INPUT],
    ];
  }

  /**
   * dataLimit
   */
  public static function dataLimit()
  {
    return [
      ['0',   self::TEST_INPUT, '...'],
      ['1',   self::TEST_INPUT, 'L...'],
      ['5',   self::TEST_INPUT, 'Lorem...'],
      ['10',  self::TEST_INPUT, 'Lorem ipsu...'],
      ['100', self::TEST_INPUT, self::TEST_INPUT],
    ];
  }

  /**
   * dataLimitEnd
   */
  public static function dataLimitEnd()
  {
    return [
      ['0',   '!',     self::TEST_INPUT, '!'],
      ['1',   '!',     self::TEST_INPUT, 'L!'],
      ['5',   '!!',    self::TEST_INPUT, 'Lorem!!'],
      ['10',  '!!!',   self::TEST_INPUT, 'Lorem ipsu!!!'],
      ['100', '!',     self::TEST_INPUT, self::TEST_INPUT],
      ['1',   '',      self::TEST_INPUT, 'L'],
      ['1',   'false', self::TEST_INPUT, 'L'],
    ];
  }

  // Tests
  // ---------------------------------------------------------------------------

  #[DataProvider("dataNoParams")]
  #[Group("modify-limit")]
  public function testNoParams($input, $expected)
  {
    $sortie = new Sortie('[foo->limit]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }

  #[DataProvider("dataLimit")]
  #[Group("modify-limit")]
  public function testLimit($limit, $input, $expected)
  {
    $sortie = new Sortie("[foo->limit:{$limit}]");

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }

  #[DataProvider("dataLimitEnd")]
  #[Group("modify-limit")]
  public function testLimitEnd($limit, $end, $input, $expected)
  {
    $sortie = new Sortie("[foo->limit:{$limit}:{$end}]");

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}

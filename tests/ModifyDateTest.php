<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyDateTest extends AbstractTestCase
{
  const TEST_ATOM     = '2010-01-01T00:00:00+00:00';
  const TEST_DATETIME = '2010-01-01 00:00:00';
  const TEST_DEFAULT  = '01/01/2010';

  // Data Providers
  // ---------------------------------------------------------------------------

  /**
   * dataNoFormat
   */
  public static function dataNoFormat()
  {
    return [
      [self::TEST_DATETIME, self::TEST_DEFAULT],
      [self::TEST_ATOM,     self::TEST_DEFAULT],
    ];
  }

  /**
   * dataFormat
   */
  public static function dataFormat()
  {
    return [
      // Quick...
      ['ATOM',            self::TEST_DATETIME, self::TEST_ATOM],
      ['ISO8601',         self::TEST_DATETIME, self::TEST_ATOM],
      ['RFC3339',         self::TEST_DATETIME, self::TEST_ATOM],
      ['datetime',        self::TEST_ATOM,     self::TEST_DATETIME],
      // Custom...
      ['"n/j/Y @ g:i a"', self::TEST_DATETIME, '1/1/2010 @ 12:00 am'],
      ['"Ymd"',           self::TEST_DATETIME, '20100101'],
      ["'Ymd'",           self::TEST_DATETIME, '20100101'],
      ['"md"',           '9-Sep', '0909'],
      ['"md"',           '1-Feb', '0201'],
    ];
  }

  // Tests
  // ---------------------------------------------------------------------------

  #[DataProvider("dataNoFormat")]
  public function testNoFormat($input, $expected)
  {
    $sortie = new Sortie('[foo->date]');

    $this->assertSame($expected, $sortie->process(['foo' => $input], true));
  }

  #[DataProvider("dataFormat")]
  public function testFormat($format, $input, $expected)
  {
    $sortie = new Sortie("[foo->date:{$format}]");

    $this->assertSame($expected, $sortie->process(['foo' => $input], true));
  }

  public function testEdgeCase1()
  {
    $sortie = new Sortie('[foo->replace:\'/^(.*)$/\':${1}-1970->date]');

    $this->assertSame('01/02/1970', $sortie->process(['foo' => '02-01'], true));
  }
}

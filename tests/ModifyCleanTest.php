<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyCleanTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      [' foo  bar   baz    ', 'foo bar baz'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->clean]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}

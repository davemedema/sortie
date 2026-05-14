<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyEmailTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      ['foo@bar.com', 'foo@bar.com'],
      ['FOO@BAR.COM', 'foo@bar.com'],
      ['foo@bar.com,baz@qux.com', 'foo@bar.com'],
      ['foo@bar.com , baz@qux.com', 'foo@bar.com'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->email]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}

<?php
namespace Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Sortie\Sortie;

class ModifyDrivetrainTest extends AbstractTestCase
{
  /**
   * data
   */
  public static function data()
  {
    return [
      ['4WD', '4X4'],
      ['AWD', 'AWD'],
      ['FWD', 'FWD'],
      ['RWD', 'RWD'],
      ['FOO', 'Other'],
      ['BAR', 'Other'],
    ];
  }

  #[DataProvider("data")]
  public function test($input, $expected)
  {
    $sortie = new Sortie('[foo->drivetrain]');

    $this->assertSame($expected, $sortie->process(['foo' => $input]));
  }
}

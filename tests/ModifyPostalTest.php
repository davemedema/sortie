<?php
namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifyPostalTest extends AbstractTestCase
{
  /**
   * test
   */
  #[Group("modify-postal")]
  public function test()
  {
    // Invalid...
    $sortie = new Sortie('[foo->postal]');

    $actual = $sortie->process(['foo' => 'abc']);

    $this->assertSame('', $actual);

    // US 5-digit...
    $sortie = new Sortie('[foo->postal]');

    $actual = $sortie->process(['foo' => '12345']);

    $this->assertSame('12345', $actual);

    // US 9-digit...
    $sortie = new Sortie('[foo->postal]');

    $actual = $sortie->process(['foo' => '12345-6789']);

    $this->assertSame('12345-6789', $actual);

    // CA...
    $sortie = new Sortie('[foo->postal:CA]');

    $actual = $sortie->process(['foo' => 'R2N 3Y2']);

    $this->assertSame('R2N 3Y2', $actual);

    // UK...
    $sortie = new Sortie('[foo->postal:UK]');

    $actual = $sortie->process(['foo' => 'WC2N 5DU']);

    $this->assertSame('', $actual);
  }
}

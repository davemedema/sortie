<?php
namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifyReplaceTest extends AbstractTestCase
{
  /**
   * test
   */
  #[Group("modify-replace")]
  public function test()
  {
    // Default...
    $sortie = new Sortie("[foo->replace]");

    $actual = $sortie->process(['foo' => 'Foo 123 Bar 123 Baz'], true);

    $this->assertSame('Foo 123 Bar 123 Baz', $actual);

    // Basic...
    $sortie = new Sortie("[foo->replace:'/\d+/':'000']");

    $actual = $sortie->process(['foo' => 'Foo 123 Bar 123 Baz'], true);

    $this->assertSame('Foo 000 Bar 000 Baz', $actual);

    // Complex...
    $sortie = new Sortie("http://[foo->replace:'/\/$/':'']/bar.html");

    $actual = $sortie->process(['foo' => 'foo.com/'], true);

    $this->assertSame('http://foo.com/bar.html', $actual);

    // Raw pipe...
    $sortie = new Sortie("[foo->replace:'/%LP%foo|bar%RP%/':'baz']");

    $actual = $sortie->process(['foo' => 'bar'], true);

    $this->assertSame('bar', $actual);

    // Escaped pipe...
    $sortie = new Sortie("[foo->replace:'/%LP%foo%PI%bar%RP%/':'baz']");

    $actual = $sortie->process(['foo' => 'bar'], true);

    $this->assertSame('baz', $actual);

    // Brackets...
    $sortie = new Sortie("[foo->replace:'/%LB%f%RB%oo/':'FOO']");

    $actual = $sortie->process(['foo' => 'foo bar baz'], true);

    $this->assertSame('FOO bar baz', $actual);

    // Colon
    $sortie = new Sortie("[foo->replace:'/%CN%/':'FOO']");

    $actual = $sortie->process(['foo' => 'foo : baz'], true);

    $this->assertSame('foo FOO baz', $actual);

    // Asterisks
    $sortie = new Sortie("[foo->replace:'/\*+/':'']");

    $actual = $sortie->process(['foo' => '*foo*'], true);

    $this->assertSame('foo', $actual);

    // Subpattern
    $sortie = new Sortie('[foo->replace:\'/^Foo\s(123)\sBar\s(456).*$/\':\'${1}-${2}\']');

    $actual = $sortie->process(['foo' => 'Foo 123 Bar 456 Baz'], true);

    $this->assertSame('123-456', $actual);

    // Real-World Example
    $sortie = new Sortie('[WebsiteVDPURL->replace:\'#carsforsale\.com/vehicle/details#i\':\'carwizedetroit.com/Inventory/Details\']');

    $actual = $sortie->process(['WebsiteVDPURL' => 'https://www.carsforsale.com/vehicle/details/foo/bar/'], true);

    $this->assertSame('https://www.carwizedetroit.com/Inventory/Details/foo/bar/', $actual);

    // Real-World Example
    $sortie = new Sortie('[Stock->replace:\'/^%LP%%LB%WVTSPR%RB%%PI%cc%RP%?%LB%0-9%RB%+$/i\':\'MATCH\']');
    $actual = $sortie->process(['Stock' => '6534'], true);
    $this->assertSame('MATCH', $actual);

    // Real-World Example
    $sortie = new Sortie('[certified->replace:\'/True/i\':\'MATCH\']');
    $actual = $sortie->process(['certified' => 'True'], true);
    $this->assertSame('MATCH', $actual);
  }
}

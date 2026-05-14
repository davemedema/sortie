<?php
namespace Tests;

use PHPUnit\Framework\Attributes\Group;
use Sortie\Sortie;
use Tests\AbstractTestCase;

class ModifyMatchTest extends AbstractTestCase
{
  /**
   * test
   */
  #[Group("modify-match")]
  public function test()
  {
    // Default...
    $sortie = new Sortie("[foo->match]");

    $actual = $sortie->process(['foo' => 'Foo 123 Bar 456 Baz']);

    $this->assertSame('Foo 123 Bar 456 Baz', $actual);

    // No match...
    $sortie = new Sortie("[foo->match:'/Bar/']");

    $actual = $sortie->process(['foo' => 'Foo 123 456 Baz']);

    $this->assertSame('', $actual);

    // Full pattern match...
    $sortie = new Sortie("[foo->match:'/Bar/']");

    $actual = $sortie->process(['foo' => 'Foo 123 Bar 456 Baz']);

    $this->assertSame('Bar', $actual);

    // Subpattern match...
    $sortie = new Sortie("[foo->match:'/123\s(Bar)/:1']");

    $actual = $sortie->process(['foo' => 'Foo 123 Bar 456 Baz']);

    $this->assertSame('Bar', $actual);

    // Invalid subpattern index...
    $sortie = new Sortie("[foo->match:'/123\s(Bar)/:2']");

    $actual = $sortie->process(['foo' => 'Foo 123 Bar 456 Baz']);

    $this->assertSame('', $actual);

    // URL...
    $sortie = new Sortie("[foo->match:'#http%CN%//www\.example\.com/Inventory/Details/\d+#i']");

    $actual = $sortie->process(['foo' => 'Lorum ipsum dolor sit http://www.example.com/Inventory/Details/48489564']);

    $this->assertSame('http://www.example.com/Inventory/Details/48489564', $actual);

    // IRL Example 01
    $sortie = new Sortie("[if(styledescription->match:'/SEL 2\.0/'=\"SEL 2.0\"){\"SEL 2.0T\"}else{trim}]");

    $actual = $sortie->process([
      'styledescription' => 'foo SEL 2.0 bar',
      'trim' => 'TRIM',
    ]);

    $this->assertSame('SEL 2.0T', $actual);

    // IRL Example 02
    $sortie = new Sortie("[foo->match:'/^(.*?)heated(.*)seats(.*?)$/']");

    $actual = $sortie->process(['foo' => 'foo heated bar seats baz']);

    $this->assertSame('foo heated bar seats baz', $actual);

    // IRL Example 03
    $pattern = '/heated%LB%^,%RB%+seats/isu';

    $sortie = new Sortie("[foo->match:'{$pattern}']");

    $actual1 = $sortie->process(['foo' => 'heated steering wheel, power seats']);
    $actual2 = $sortie->process(['foo' => 'power steering, heated seats, all-wheel drive']);

    $this->assertSame('', $actual1);
    $this->assertSame('heated seats', $actual2);
  }
}

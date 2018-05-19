<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use ReportCollection\Libs\Reader;
use ReportCollection\Tests\Libs;

class ReaderToXmlTest extends TestCase
{
    public function testSave()
    {
        $provider = array(
            ["Company", "Contact", "Country"],
            ["Alfreds Futterkiste", "Maria Anders", "Germany"],
            ["Centro comercial Moctezuma", "Francisco Chang", "Mexico"],
            ["Ernst Handel", "Roland Mendel", "Austria"],
            ["Island Trading", "Helen Bennett", "UK"],
            ["Laughing Bacchus Winecellars", "Yoshi Tannamuri", "Canada"],
            ["Magazzini Alimentari Riuniti", "Giovanni Rovelli", "Italy"]
        );

        $handle = Reader::createFromArray($provider);

        $this->assertEquals("<"."?xml version=\"1.0\"?".">
<Table>
  <Row>
    <Cell>Company</Cell>
    <Cell>Contact</Cell>
    <Cell>Country</Cell>
  </Row>
  <Row>
    <Cell>Alfreds Futterkiste</Cell>
    <Cell>Maria Anders</Cell>
    <Cell>Germany</Cell>
  </Row>
  <Row>
    <Cell>Centro comercial Moctezuma</Cell>
    <Cell>Francisco Chang</Cell>
    <Cell>Mexico</Cell>
  </Row>
  <Row>
    <Cell>Ernst Handel</Cell>
    <Cell>Roland Mendel</Cell>
    <Cell>Austria</Cell>
  </Row>
  <Row>
    <Cell>Island Trading</Cell>
    <Cell>Helen Bennett</Cell>
    <Cell>UK</Cell>
  </Row>
  <Row>
    <Cell>Laughing Bacchus Winecellars</Cell>
    <Cell>Yoshi Tannamuri</Cell>
    <Cell>Canada</Cell>
  </Row>
  <Row>
    <Cell>Magazzini Alimentari Riuniti</Cell>
    <Cell>Giovanni Rovelli</Cell>
    <Cell>Italy</Cell>
  </Row>
</Table>
", $handle->toXml());

    }
}

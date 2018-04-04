<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpersTest extends TestCase
{
    public function testColumnVowel()
    {
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $alpha = 26; // numero de letras no alfabeto

        $this->assertEquals('A', $handle->getColumnVowel(1));
        $this->assertEquals('Z', $handle->getColumnVowel($alpha));
        $this->assertEquals('AA', $handle->getColumnVowel($alpha + 1));
        $this->assertEquals('AZ', $handle->getColumnVowel($alpha * 2));
        $this->assertEquals('BA', $handle->getColumnVowel($alpha * 2 + 1));
        $this->assertEquals('BZ', $handle->getColumnVowel($alpha * 3));

        $this->assertEquals('A', $handle->getColumnVowel('A'));
        $this->assertEquals('Z', $handle->getColumnVowel('Z'));
        $this->assertEquals('ZA', $handle->getColumnVowel('ZA'));
    }

    public function testColumnNumber()
    {
        $handle = \ReportCollection::createFromArray(array(["Company", "Contact", "Country"]));

        $alpha = 26; // numero de letras no alfabeto

        $this->assertEquals(1, $handle->getColumnNumber('A'));
        $this->assertEquals($alpha, $handle->getColumnNumber('Z'));
        $this->assertEquals($alpha + 1, $handle->getColumnNumber('AA'));
        $this->assertEquals($alpha * 2, $handle->getColumnNumber('AZ'));
        $this->assertEquals($alpha * 2 + 1, $handle->getColumnNumber('BA'));
        $this->assertEquals($alpha * 3, $handle->getColumnNumber('BZ'));

        $this->assertEquals(1, $handle->getColumnNumber(1));
        $this->assertEquals($alpha, $handle->getColumnNumber(26));
        $this->assertEquals(49, $handle->getColumnNumber(49));
    }
}

<?php

namespace ReportCollection\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpersTest extends TestCase
{
    public function testParsing()
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
}

<?php

namespace Matthias\SymfonyServiceDefinitionValidator\Tests\Error;

use Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorList;
use PHPUnit\Framework\TestCase;

class ValidationErrorListTest extends TestCase
{
    public function testAddsErrorToList()
    {
        $list = new ValidationErrorList();

        $error1 = $this->createMockError();
        $error2 = $this->createMockError();

        $list->add($error1);
        $list->add($error2);

        $this->assertCount(2, $list);
        $expectedErrors = array($error1, $error2);

        $this->assertSame($expectedErrors, iterator_to_array($list));
    }

    private function createMockError()
    {
        return $this->createMock('Matthias\SymfonyServiceDefinitionValidator\Error\ValidationErrorInterface');
    }
}

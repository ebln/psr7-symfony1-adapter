<?php

namespace brnc\Tests\Symfony1\Message\Implementation;

use brnc\Symfony1\Message\Obligation\NoSfWebRequestException;
use brnc\Symfony1\Message\Obligation\sfWebRequestSubsetInterface;
use brnc\Symfony1\Message\Obligation\sfWebRequestSubsetProxy;
use PHPUnit\Framework\TestCase;

class sfWebRequestSubsetProxyTest extends TestCase
{
    public function test__construct()
    {
        $possibleRequest = $this->prophesize(sfWebRequestSubsetInterface::class)->reveal();
        $this->assertInstanceOf(sfWebRequestSubsetProxy::class, sfWebRequestSubsetProxy::create($possibleRequest));
    }

    /**
     * @dataProvider getFailingConstructionData
     *
     * @param mixed  $argument
     * @param string $expectedMessage
     */
    public function testFailingConstructor($argument, $expectedMessage)
    {
        $this->expectException(NoSfWebRequestException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->assertInstanceOf(sfWebRequestSubsetProxy::class, sfWebRequestSubsetProxy::create($argument));
    }

    /**
     * @return array
     */
    public function getFailingConstructionData()
    {
        return [
            'null' => [
                'argument'         => null,
                'expected message' => 'Expected sfWebRequest as argument! NULL provided.',
            ],
            '1337' => [
                'argument'         => 1337,
                'expected message' => 'Expected sfWebRequest as argument! integer provided.',
            ],
            'stdClass' => [
                'argument'         => (object)['foo bar'],
                'expected message' => 'Expected sfWebRequest as argument! stdClass provided.',
            ],
            'error class' => [
                'argument'         => new NoSfWebRequestException(),
                'expected message' => 'Expected sfWebRequest as argument! ' . NoSfWebRequestException::class . ' provided.',
            ],
        ];
    }
}

<?php /** @noinspection PhpUndefinedMethodInspection */

namespace brnc\Tests\Symfony1\Message\Obligation;

use brnc\Symfony1\Message\Obligation\SfParameterHolderSubsetInterface;
use brnc\Symfony1\Message\Obligation\SfWebRequestSubsetInterface;
use Prophecy;

/**
 * common mock trait for sfWebRequestSubsetInterface
 */
trait MockSfWebRequestSubsetTrait
{
    /**
     * @param mixed $method
     * @param mixed $version
     * @param array $headers
     *
     * @return SfWebRequestSubsetInterface|Prophecy\Doubler\DoubleInterface
     */
    protected function createSfWebRequestSubsetMock($method, $version, array $headers)
    {
        $requestProphecy = $this->prophesize(SfWebRequestSubsetInterface::class);

        // mock getHttpHeader
        $lowerCaseHeaders = array_combine(array_map('strtolower', array_keys($headers)), array_values($headers));
        $requestProphecy->getHttpHeader(Prophecy\Argument::any())->willReturn(null);
        $requestProphecy->getHttpHeader(Prophecy\Argument::type('string'))
            ->will(function($args) use ($lowerCaseHeaders) {
                $normalizedName = strtolower($args[0]);
                /** @var string[] $headers */
                if (isset($lowerCaseHeaders[$normalizedName])) {
                    return $lowerCaseHeaders[$normalizedName];
                }

                return null;
            });

        // mock getPathInfoArray() and opaque version
        $httpHeaders                    = array_combine(array_map(function($v) {
            return 'HTTP_' . str_replace('-', '_', strtoupper($v));
        }, array_keys($headers)), array_values($headers));
        $httpHeaders['SERVER_PROTOCOL'] = 'HTTP/' . $version;
        $requestProphecy->getPathInfoArray()->willReturn($httpHeaders);

        // mock method()
        $requestProphecy->getMethod()->willReturn($method);

        // mock now-unused other methods returning arrays
        $requestProphecy->getOptions()->willReturn([]);
        $requestProphecy->getRequestParameters()->willReturn([]);
        $requestProphecy->getGetParameters()->willReturn([]);
        $requestProphecy->getPostParameters()->willReturn([]);
        // mock now-unused other methods returning sfParameterHolder
        $attributeHolderMock = $this->prophesize(SfParameterHolderSubsetInterface::class);
        $attributeHolderMock->getAll()->willReturn([]);
        $requestProphecy->getAttributeHolder()->willReturn($attributeHolderMock->reveal());
        $parameterHolderMock = $this->prophesize(SfParameterHolderSubsetInterface::class);
        $parameterHolderMock->getAll()->willReturn([]);
        $requestProphecy->getParameterHolder()->willReturn($parameterHolderMock->reveal());

        return $requestProphecy->reveal();
    }
}

<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response;

class OptionsTranscriptor implements OptionsTranscriptorInterface
{
    /** @param array<string, mixed> $options */
    public function transcribeOptions(\sfWebResponse $sfWebResponse, array $options): void
    {
        $options              = array_merge($sfWebResponse->getOptions(), $options);
        $reflexiveWebResponse = new \ReflectionObject($sfWebResponse);
        $reflexOptions        = $reflexiveWebResponse->getProperty('options');
        $reflexOptions->setAccessible(true);
        $reflexOptions->setValue($sfWebResponse, $options);
        $reflexOptions->setAccessible(false);
    }
}

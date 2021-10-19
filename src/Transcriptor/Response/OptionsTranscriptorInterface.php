<?php

declare(strict_types=1);

namespace brnc\Symfony1\Message\Transcriptor\Response;

/**
 * Meant to make calls to OptionsTranscriptor testable
 *
 * As the OptionsTranscriptor is heavily relying on reflection,
 * this interface is supposed to make it testable
 */
interface OptionsTranscriptorInterface
{
    /** @param array<string, mixed> $options */
    public function transcribeOptions(\sfWebResponse $sfWebResponse, array $options): void;
}

<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A generálás sikertelen (kvóta, elérhetetlenség, időtúllépés, érvénytelen válasz). A felhasználó csak barátságos
 * üzenetet lát (US-07 AC6); a kivétel szövege technikai, és nem tartalmaz gyereknevet vagy meseszöveget (CLAUDE.md).
 *
 * Generation failed (quota, unavailable, timeout, invalid answer). The user only sees a friendly message
 * (US-07 AC6); the exception message is technical and never contains the child's name or story text (CLAUDE.md).
 */
class StoryGenerationException extends RuntimeException
{
    public const INVALID_RESPONSE = 'invalid_response';
    public const UNAVAILABLE = 'unavailable';

    public string $reason;

    public function __construct(string $reason, string $message = '')
    {
        parent::__construct($message ?: $reason);
        $this->reason = $reason;
    }
}

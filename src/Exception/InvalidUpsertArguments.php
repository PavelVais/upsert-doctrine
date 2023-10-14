<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Exception;

use Doctrine\DBAL\Exception;

class InvalidUpsertArguments extends Exception
{
    public static function notSupportedAttribute(): self
    {
        return new self('Invalid data for upsert.');
    }

    public static function invalidAttribute(string $attribute): self
    {
        return new self(sprintf('Invalid attribute "%s" for upsert.', $attribute));
    }
}

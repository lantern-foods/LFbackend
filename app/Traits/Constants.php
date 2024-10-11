<?php

namespace App\Traits;

final class Constants
{
    public const OTYPE_PENDING_P = 'PENDING_PAYMENT';
    public const OTYPE_PENDING_A = 'PENDING_ACCEPTANCE';
    public const OTYPE_CONFIRMED = 'CONFIRMED';
    public const OTYPE_PREPARED = 'PREPARED';
    public const OTYPE_ONWAY = 'ON_THE_WAY';
    public const OTYPE_COMPLETED = 'COMPLETED';
    public const OTYPE_CANCELLED = 'CANCELLED';

    /**
     * Get all available order types
     *
     * @return array
     */
    public static function allOrderTypes(): array
    {
        return [
            self::OTYPE_PENDING_P,
            self::OTYPE_PENDING_A,
            self::OTYPE_CONFIRMED,
            self::OTYPE_PREPARED,
            self::OTYPE_ONWAY,
            self::OTYPE_COMPLETED,
            self::OTYPE_CANCELLED,
        ];
    }
}

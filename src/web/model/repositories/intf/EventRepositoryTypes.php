<?php
declare(strict_types=1);

class EventsSearchCriteria
{
    public function __construct(
        public readonly bool $userIsAdmin,
        public readonly ?int $userId,
    ) {}
}

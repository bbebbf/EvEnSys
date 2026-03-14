<?php
declare(strict_types=1);

class EventDto
{
    public function __construct(
        public readonly int     $eventId,
        public readonly string  $eventGuid,
        public readonly int     $creatorUserId,
        public readonly bool    $eventIsActivated,
        public readonly bool    $eventIsVisible,
        public readonly string  $eventTitle,
        public readonly ?string $eventDescription,
        public readonly \DateTimeImmutable $eventDate,
        public readonly ?string $eventLocation,
        public readonly ?float  $eventDurationHours,
        public readonly ?int    $eventMaxSubscriber,
        public readonly ?\DateTimeImmutable $eventCreatedDate = null,
        public readonly ?string $creatorName = null,
        public readonly ?string $eventResponsible = null,
    ) {}

    public function getResponsibleName(): ?string
    {
        if ($this->eventResponsible !== null && trim($this->eventResponsible) !== '') {
            return $this->eventResponsible;
        }
        if ($this->creatorName !== null) {
            return $this->creatorName;
        }
        return null;
    }
}

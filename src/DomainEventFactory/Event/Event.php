<?php

namespace DomainEventFactory\Event;

use DomainEventFactory\Infrastructure\ArrayableInterface;

class Event implements ArrayableInterface, EventInterface, \JsonSerializable
{
    /**
     * @var EventObjectInterface $object
     */
    private $object;
    /**
     * @var string $objectHash;
     */
    private $objectHash;
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var array $payload
     */
    private $payload = [];
    /**
     * Event constructor.
     * @param string $name
     * @param EventObjectInterface $object
     * @param array $payload
     */
    public function __construct(
        string $name,
        EventObjectInterface $object,
        array $payload
    ) {
        $this->name = $name;
        $this->object = $object;
        $this->objectHash = spl_object_hash($object);
        $this->payload = $payload;
    }
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @inheritdoc
     */
    public function getObject(): EventObjectInterface
    {
        return $this->object;
    }
    /**
     * @inheritdoc
     */
    public function getObjectHash(): string
    {
        return $this->objectHash;
    }
    /**
     * @inheritdoc
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->payload;
    }
    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    /**
     * @param Metadata[] $metadataObjects
     * @return Event[]
     */
    public static function createFromMetadata(array $metadataObjects): array
    {
        $temp = [];
        /** @var Metadata $metadata */
        foreach ($metadataObjects as $metadata) {
            $temp[] = new Event(
                $metadata->getName(),
                $metadata->getObject(),
                $metadata->getMetadata()
            );
        }

        return $temp;
    }
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
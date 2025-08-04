<?php

declare(strict_types= 1);

namespace IronFlow\Core\Module;

/**
 * Informations sur un module
 */
final class ModuleInfo
{
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly string $description = '',
        public readonly array $authors = [],
        public readonly array $tags = []
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'authors' => $this->authors,
            'tags' => $this->tags
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            '%s v%s - %s',
            $this->name,
            $this->version,
            $this->description
        );
    }
}
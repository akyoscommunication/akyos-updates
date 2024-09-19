<?php

namespace AkyosUpdates\Class;

class Hook
{
    private ?string $type;
    private ?string $hook;
    private mixed $component;
    private ?string $callback;
    private ?int $priority;
    private ?array $accepted_args;

    public function __construct(?string $type = null, ?string $hook = null, mixed $component = null, ?string $callback = null, ?int $priority = 10, ?array $accepted_args = [])
    {
        $this->type = $type;
        $this->hook = $hook;
        $this->component = $component;
        $this->callback = $callback;
        $this->priority = $priority;
        $this->accepted_args = $accepted_args;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getHook(): ?string
    {
        return $this->hook;
    }

    public function setHook(?string $hook): self
    {
        $this->hook = $hook;

        return $this;
    }

    public function getComponent(): mixed
    {
        return $this->component;
    }

    public function setComponent(mixed $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function getCallback(): ?string
    {
        return $this->callback;
    }

    public function setCallback(?string $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getAcceptedArgs(): ?array
    {
        return $this->accepted_args;
    }

    public function setAcceptedArgs(?array $accepted_args): self
    {
        $this->accepted_args = $accepted_args;

        return $this;
    }

}
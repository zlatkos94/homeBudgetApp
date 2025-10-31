<?php

namespace App\Search;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSearch implements SearchRequestInterface
{
    public const LIMIT = 20;
    public const DEFAULT_ORDER_BY = 'id';
    public const DEFAULT_DIRECTION = 'ASC';

    public const DIRECTION = 'ASC';
    public const ORDER_BY = 'id';
    protected int $limit;
    private ?int $position;
    private ?string $orderBy;
    private ?string $direction;
    private ?string $like;
    protected ?Request $request;

    public function __construct(Request $requestStack)
    {
        $this->request = $requestStack;
        $query = $this->request->query->get('like');
        $this->like = ($query !== null) ? trim(preg_replace('/\s+/', ' ', strtolower($query))) : null;
        $this->limit = max(0, min((int) $this->request->query->get('limit', self::LIMIT), self::LIMIT));
        $this->orderBy = $this->request->query->get('orderBy');
        $directionFromRequest = $this->request->query->get('direction');
        $this->direction = $directionFromRequest !== null && in_array(strtoupper($directionFromRequest), ['ASC', 'DESC'])
            ? strtoupper($directionFromRequest)
            : null;
        $position = $this->request->query->get('position');
        $this->position = ($position === null || $position === '')  ?
                null :
                max(0, (int) $position);
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function getLike(): ?string
    {
        return $this->like ?? null;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}

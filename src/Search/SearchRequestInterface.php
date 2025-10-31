<?php

namespace App\Search;

interface SearchRequestInterface
{
    public function getLimit(): int;

    public function getOrderBy(): ?string;

    public function getDirection(): ?string;

    public function getLike(): ?string;

    public function getPosition(): ?int;
}

<?php

namespace App\Search;

use Symfony\Component\HttpFoundation\Request;

class SearchRequest extends AbstractSearch
{
    public function __construct(Request $requestStack)
    {
        parent::__construct($requestStack);
    }
}

<?php

namespace App\Controllers;

use LogicException;
use App\Http\Session;
use App\Services\CategoryService;

final class CategoryController extends BaseController
{
    public function __construct(
        private CategoryService $categoryService,
        Session $session
    ) {
        parent::__construct($session);
    }
}
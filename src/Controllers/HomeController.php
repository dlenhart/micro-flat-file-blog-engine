<?php

namespace App\Controllers;

use App\Core\Blog\Interfaces\PostRepositoryInterface;

class HomeController extends Controller
{
    public function __construct(private PostRepositoryInterface $posts)
    {
    }

    public function home()
    {
        $page = (int) (params()->page ?? 1);

        return view('index', [
            'page' => $this->posts->paginate($page, config('app.posts_per_page')),
        ]);
    }
}

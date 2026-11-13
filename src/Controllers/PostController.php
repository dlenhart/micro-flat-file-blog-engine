<?php

namespace App\Controllers;

use App\Core\Blog\Interfaces\PostRepositoryInterface;

class PostController extends Controller
{
    public function __construct(private PostRepositoryInterface $posts)
    {
    }

    public function post(?string $post = null)
    {
        $slug = $post ?? (params()->post ?? null);

        if ($slug === null) {
            return notFound('404 | POST NOT FOUND.');
        }

        $found = $this->posts->find($slug);

        if ($found === null) {
            return notFound('404 | POST NOT FOUND.');
        }

        return view('post', [
            'post' => $found->content(),
            'meta' => $found->metadata(),
        ]);
    }
}

<?php
/**
 * Filter
 * @package admin-post
 * @version 0.0.1
 */

namespace AdminPost\Library;

use Post\Model\Post;

class Filter implements \Admin\Iface\ObjectFilter
{
    static function filter(array $cond): ?array{
        $cnd = [];
        if(isset($cond['q']) && $cond['q'])
            $cnd['q'] = (string)$cond['q'];
        $posts = Post::get($cnd, 15, 1, ['title'=>true]);
        if(!$posts)
            return [];

        $result = [];
        foreach($posts as $post){
            $result[] = [
                'id'    => (int)$post->id,
                'label' => $post->title,
                'info'  => $post->title,
                'icon'  => NULL
            ];
        }

        return $result;
    }

    static function lastError(): ?string{
        return null;
    }
}
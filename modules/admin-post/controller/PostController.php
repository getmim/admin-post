<?php
/**
 * PostController
 * @package admin-post
 * @version 0.0.1
 */

namespace AdminPost\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibForm\Library\Combiner;
use LibPagination\Library\Paginator;
use Post\Model\{
    Post,
    PostContent as PContent
};

class PostController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['post', 'all-post']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_post && !$this->can_i->manage_post_all)
            return $this->show404();

        $post = (object)[
            'content' => '',
            'status'  => 1
        ];

        $id = $this->req->param->id;
        if($id){
            $cond = [
                'id' => $id
            ];
            if(!$this->can_i->manage_post_all)
                $cond['user'] = $this->user->id;
            $post = Post::getOne(['id'=>$id]);
            if(!$post)
                return $this->show404();
            $params = $this->getParams('Edit Post');

            $content = PContent::getOne(['post'=>$id]);
            $post->content = $content ? $content->text : '';
        }else{
            $params = $this->getParams('Create New Post');
        }

        $form              = new Form('admin.post.edit');
        $params['form']    = $form;

        $params['statuses'] = [
            1 => 'Draft',
            2 => 'Editor'
        ];

        if($this->can_i->publish_post)
            $params['statuses'][3] = 'Published';

        $c_opts = [
            'cover'      => [null,                  null, 'json'],
            'meta'       => [null,                  null, 'json'],
            'category'   => ['admin-post-category', null, 'format', 'all', 'name', 'parent'],
            'gallery'    => ['admin-post-gallery',  null, 'format', 'active', 'title'],
            'website'    => ['admin-post-website',  null, 'format', 'all', 'name']
        ];

        $combiner = new Combiner($id, $c_opts, 'post');
        $post    = $combiner->prepare($post);

        $params['opts'] = $combiner->getOptions();
        
        if(!($valid = $form->validate($post)) || !$form->csrfTest('noob'))
            return $this->resp('post/edit', $params);
        
        $valid = $combiner->finalize($valid);
        
        $checkboxes = [
            'feature_post'     => 'featured',
            'editor_pick_post' => 'editor_pick'
        ];
        foreach($checkboxes as $perm => $prop){
            if(!$this->can_i->$perm){
                if(isset($valid->$prop))
                    unset($valid->$prop);
            }else{
                if(isset($valid->$prop))
                    $valid->$prop = false;
            }
        }

        if(!$this->can_i->publish_post){
            if($valid->status == 3 && $post->status != 3)
                $valid->status = $post->status;
        }

        $content = $valid->content ?? '';
        unset($valid->content);

        $valid->words = str_word_count($content);
        
        if($id){
            if(!Post::set((array)$valid, ['id'=>$id]))
                deb(Post::lastError());
            PContent::set(['text'=>$content],['post'=>$id]);
        }else{
            $valid->user = $this->user->id;
            if(!($id = Post::create((array)$valid)))
                deb(Post::lastError());

            PContent::create([
                'post' => $id,
                'text' => $content
            ]);
        }

        $valid->content = $content;

        $combiner->save($id, $this->user->id);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => $id ? 2 : 1,
            'type'   => 'post',
            'original' => $post,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminPost');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_post && !$this->can_i->manage_post_all)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;

        if($status = $this->req->getQuery('status'))
            $pcond['status'] = $cond['status'] = $status;
        else
            $cond['status'] = ['__op', '>', 0];

        if(!$this->can_i->manage_post_all)
            $cond['user'] = $this->user->id;

        list($page, $rpp) = $this->req->getPager(25, 50);

        $posts = Post::get($cond, $rpp, $page, ['title'=>true]) ?? [];
        if($posts)
            $posts = Formatter::formatMany('post', $posts, ['user']);

        $params          = $this->getParams('Post');
        $params['posts'] = $posts;
        $params['form']  = new Form('admin.post.index');

        $params['form']->validate( (object)$this->req->get() );

        // pagination
        $params['total'] = $total = Post::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminPost'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('post/index', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->remove_post)
            return $this->show404();

        $id    = $this->req->param->id;
        $post  = Post::getOne(['id'=>$id]);
        $next  = $this->router->to('adminPost');
        $form  = new Form('admin.post.index');

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'post',
            'original' => $post,
            'changes'  => null
        ]);

        $post_set = [
            'status' => 0,
            'slug'   => time() . '#' . $post->slug
        ];
        Post::set($post_set, ['id'=>$id]);

        $this->res->redirect($next);
    }
}
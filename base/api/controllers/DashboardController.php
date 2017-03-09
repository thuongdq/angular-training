<?php

class DashboardController {
    public function index() {
        Response::update('posts', Post::getPosts(1, 2, false));
        Response::update('categories', Category::getCategories(false));
        Response::printData();
    }
    
    public function comment($id) {        
        $comment = Input::get('comment');
        $id = intval($id);
        
        if ($id <= 0) {
            Response::warning('Có gì đó không đúng ở đây?');
        }

        if (!$comment) {
            Response::warning('Nội dung comment không được bỏ trống');
        }
        

        if (Response::isValid()) {
            $post = Post::create([
            'title' => '',
            'content' => $comment,            
            'user_id' => Session::get('user')['id'],
            'parent_id' => $id          
            ]);        
            Response::messages("Comment thành công");
        }
        Response::printData();
    }
    
    public function detail($id) {
        $post = Post::getPost($id);
        $categories = Category::getCategories(false);
        $comments = Post::getComments($id);
        Session::update('hash', App::getHash());
        if ($post) {
            Response::update('post', $post);
            Response::update('categories', $categories);
            Response::update('comments', $comments);
        }
        Response::printData();
    }
    
    public function vote($id) {
        if (Input::get()) {
            $post = Post::find($id);
            Response::messages('Xóa thành công câu hỏi "' . $post->title . '"');
            $post->delete();
        }
        Response::printData();
    }
    
    public function creator() {
        Response::update('categories', Category::getCategories(false));
        Session::update('hash', App::getHash());
        Response::printData();
    }

    public function getComments($id) {
        $comments = Post::getComments($id);
        if ($comments) {
            Response::update('comments', $comments);
        }
        Response::printData();
    }

    public function getPostsByCategory($id) {
        Response::update('posts', Post::getPostsByCategory($id, 1, 2));        
        Response::printData();
    }
}
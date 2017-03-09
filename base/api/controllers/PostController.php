<?php

class PostController {
    public function index() {
        Response::update('posts', Post::getPosts(10));
        Response::printData();
    }
    
    public function create() {
        $title = Input::get('title');
        $content = Input::get('content');
        $category_id = Input::get('category_id');
        $tags = Input::get('tags');
        
        if (!$title) {
            Response::warning('Tên câu hỏi không được bỏ trống');
        }
        
        if ($title && Post::getPost($title)) {
            Response::warning('Tên câu hỏi đã trùng, vui lòng chọn câu hỏi khác');
        }
        
        if (!$content) {
            Response::warning('Nội dung câu hỏi không được bỏ trống');
        }
        
        
        if (!$category_id) {
            Response::warning('Vui lòng chọn chuyên mục dùm cái!');
        }
        
        if (Response::isValid()) {
            $post = Post::create([
            'title' => $title,
            'content' => $content,
            'category_id' => $category_id,
            'user_id' => Session::get('user')['id']            
            ]);
            
            if ($tags) {
                $tags = explode(',', $tags);
                foreach ($tags as $key => $value) {
                    $tag = mb_strtolower(trim($value));
                    $current_tag = Tag::find( 'first', [ 'conditions' => [ 'name = ? OR alias = ?', $tag, Tool::VNConvert($tag) ] ] );
                    if (!$current_tag) {
                        $tag = Tag::create([
                        'name' => $tag,
                        'alias' => Tool::VNConvert($tag)
                        ]);
                    } else {
                        PostTag::create([
                        'post_id' => $post->id,
                        'tag_id' => $current_tag->id
                        ]);
                    }
                }
            }
            Response::messages("Tạo câu hỏi \"$title\" thành công");
        }
        Response::printData();
    }
    
    public function update($id) {
        $title = Input::get('title');
        $content = Input::get('content');
        $category_id = Input::get('category_id');
        $tags = Input::get('tags');
        
        if (!$title) {
            Response::warning('Tên câu hỏi không được bỏ trống');
        }
        
        $current_post = Post::find($id);
        $another_post = Post::getPost($title);
        if ($title && $another_post && $another_post['id'] != $id) {
            Response::warning('Bài viết đã trùng, vui lòng chọn câu hỏi khác');
        }
        
        if (!$content) {
            Response::warning('Nội dung câu hỏi không được bỏ trống');
        }
        
        
        if (!$category_id) {
            Response::warning('Vui lòng chọn chuyên mục dùm cái!');
        }
        
        if (Response::isValid()) {
            $current_post->title = $title;
            $current_post->content = $content;
            $current_post->category_id = $category_id;
            $current_post->save();
            
            if ($tags) {
                $tags = explode(',', $tags);
                foreach ($tags as $key => $value) {
                    $tag = mb_strtolower(trim($value));
                    $current_tag = Tag::find( 'first', [ 'conditions' => [ 'name = ? OR alias = ?', $tag, Tool::VNConvert($tag) ] ] );
                    if (!$current_tag) {
                        $current_tag = Tag::create([
                        'name' => $tag,
                        'alias' => Tool::VNConvert($tag)
                        ]);
                    }
                    if (!PostTag::find('first', ['conditions' => [ 'post_id = ? AND tag_id = ?', $current_post->id, $current_tag->id ] ])) {
                        PostTag::create([
                        'post_id' => $current_post->id,
                        'tag_id' => $current_tag->id
                        ]);
                    }                    
                }
            }
            Response::messages("Cập nhật câu hỏi \"$title\" thành công");
        }
        Response::printData();
    }
    
    public function show($id) {
        $post = Post::getPost($id);
        $categories = Category::getCategories(false);
        Session::update('hash', App::getHash());
        if ($post) {
            Response::update('post', $post);
            Response::update('categories', $categories);
        }
        Response::printData();
    }
    
    public function delete($id) {
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
    
    public function upload() {
        $data['errors'] = [];
        $hash = Session::get('hash');
        if (!$hash) {
            $data['errors'][] = 'Có lỗi xảy ra';
        } else {
            $file = $_FILES['file'];
            $verify = Upload::verify($file);
            if ($verify !== true) {
                switch ($verify) {
                    case 'maxsize':
                        $data['errors'][] = 'Dung lượng quá mức cho phép';
                        break;
                    case 'mimes':
                        $data['errors'][] = 'Định dạng không cho phép';
                        break;
            }
        }
        
        if (!$data['errors']) {
            $data['file']['temp'] = Upload::put($file, '../uploads/temp');
            $data['file']['name'] = $file['name'];
            $data['file']['hash'] = $hash;
            $time = time();
            // Tạo sự kiện
            Event::create([
            'name' => 'image',
            'action' => 'save',
            'data' => json_encode($data['file']),
            'created_at' => Tool::convertToTimestamp($time),
            'expired_at' => Tool::convertToTimestamp($time + Event::$eventExpired),
            ]);
        }
    }
    echo App::toJson($data);
}
}
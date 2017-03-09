<?php
class Post extends ActiveRecord\Model {
    static $belongs_to = [
    ['category', 'class_name' => 'Category'],
    ['user', 'class_name' => 'User']
    ];
    
    static $has_many = [
    ['post_tag'],
    ['tags', 'through' => 'post_tag']
    ];
    
    protected static $post;
    
    public static function getPost($post = '') {
        if (is_numeric($post)) {
            static::$post = self::find($post);
        } else {
            static::$post = self::find('first', ['conditions' => ['title = ?', $post]]);
        }
        
        $post = static::$post ? static::$post->attributes() : false;
        
        if ($post) {
            $post['category_id'] = strval($post['category_id']);
            $post['date'] = date('d-m-Y H:i:s', strtotime($post['created_at']));			
            $post['tags'] = array_map(function($tag) {
				return $tag->attributes();
			}, static::$post->tags);			
            return $post;
        }
        return false;
    }
    
    public static function getPosts($perPage = 10, $range = 2, $isBackend = true) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $posts = self::find('all', ['conditions' => ['parent_id = 0'], 'limit' => $perPage, 'offset' => self::getOffset($page, $perPage), 'order' => 'id desc']);
        } else {
            $posts = self::find('all');
        }
        
        if ($posts) {
            $data['data'] = [];
            foreach ($posts as $key => $post) {
                $data['data'][$key] = $post->attributes();
                $data['data'][$key]['category'] = $post->category->name;
                $data['data'][$key]['user'] = $post->user->email;
                $data['data'][$key]['date'] = date('d-m-Y', strtotime($post->created_at));
                $data['data'][$key]['slug'] = Tool::VNConvert($post->title);
                if ($post->tags) {
                    $tags = [];
                    foreach ($post->tags as $tag) {
                        $tags[] = [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'alias' => $tag->alias
                        ];
                    }
                    $data['data'][$key]['tags'] = $tags;
                }
                $data['data'][$key]['index'] = ($page - 1) * $perPage + $key;
            }
            if ($isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('posts/page/%s', $currentPage);
                });
            } elseif (!$isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('page/%s', $currentPage);
                });
            }
            return $data;
        }
        return false;
    }
    
    public static function getPostsByCategory($id = '', $perPage = 10, $range = 2) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $posts = self::find('all', [
            'limit' => $perPage,
            'offset' => self::getOffset($page, $perPage),
            'order' => 'id desc',
            'conditions' => ['category_id = ?', $id]
            ]);
        } else {
            $posts = self::find('all');
        }
        
        if ($posts) {
            $data['data'] = [];
            foreach ($posts as $key => $post) {
                $data['data'][$key] = $post->attributes();
                $data['data'][$key]['category'] = $post->category->name;
                $data['data'][$key]['user'] = $post->user->email;
                $data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($post->created_at));
                $data['data'][$key]['slug'] = Tool::VNConvert($post->title);
            }
            if ($perPage && ($totalPages = self::getPages($perPage, ['conditions' => ['category_id = ?', $id]])) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) use ($id) {
                    return sprintf('#/category/%s/page/%s', $id, $currentPage);
                });
            }
            return $data;
        }
        return false;
    }
    
    public static function getPages($perPage = 10, $conditions = []) {
        if ($conditions) {
            return ceil(self::count($conditions) / $perPage);
        } else {
            return ceil(self::count(['conditions' => ['parent_id = 0']]) / $perPage);
        }
    }
    
    public static function getOffset($page = 1, $perPage = 10) { 
        return ($page - 1)*$perPage;
    }

    public static function getComments($post_id) {
        $posts = self::find('all', ['conditions' => ['parent_id = ?', $post_id], 'order' => 'id desc']);
        
        if ($posts) {
            $data['data'] = [];
            foreach ($posts as $key => $post) {
                $data['data'][$key] = $post->attributes();                
                $data['data'][$key]['user'] = $post->user->email;
                $data['data'][$key]['date'] = date('d-m-Y', strtotime($post->created_at));                                
            }
            return $data;
        }
        return false;
    }
}
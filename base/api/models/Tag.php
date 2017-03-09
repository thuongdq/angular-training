<?php
class Tag extends ActiveRecord\Model {
    static $belongs_to = [
        ['category'],
        ['user']
    ];

    static $has_many = [
		['post_tag'],
		['posts', 'through' => 'post_tag']
	];

    public static function getTag($tag = '') {
        if (is_numeric($tag)) {
            $tag = self::find($tag)->attributes();
        } else {
            $tag = self::find('first', ['conditions' => ['title = ?', $tag]]);
            $tag = $tag ? $tag->attributes() : false;
        }

        if ($tag) {
            $tag['date'] = date('d-m-Y H:i:s', strtotime($tag['created_at']));
            return $tag;
        }
        return false;
    }

    public static function getTags($perPage = 10, $range = 2, $isBackend = true) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $tags = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage), 'order' => 'id desc']);
        } else {
            $tags = self::find('all');
        }

        if ($tags) {
            $data['data'] = [];
            foreach ($tags as $key => $tag) {
                $data['data'][$key] = $tag->attributes();
                $data['data'][$key]['posts'] = $tag->posts->title;
                $data['data'][$key]['user'] = $tag->user->email;                
                $data['data'][$key]['slug'] = Tool::VNConvert($tag->name);
                if (!file_exists('../'. $data['data'][$key]['image'])) {
                    $data['data'][$key]['image'] = 'uploads/no_image.jpg';
                }
            }
            if ($isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('#/admin/tag/page/%s', $currentPage);
                });
            } elseif (!$isBackend && $perPage && ($totalPages = self::getPages($perPage)) > 1) {
                $data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
                    return sprintf('#/page/%s', $currentPage);
                });
            }
            return $data;
        }
        return false;
    }

    public static function getTagsByCategory($id = '', $perPage = 10, $range = 2) {
        $page = Input::get('page');
        if (!$page || !is_numeric($page) || $page < 0) {
            $page = 1;
        }
        if ($perPage) {
            $tags = self::find('all', [
                'limit' => $perPage,
                'offset' => self::getOffset($page, $perPage),
                'order' => 'id desc',
                'conditions' => ['category_id = ?', $id]
            ]);
        } else {
            $tags = self::find('all');
        }

        if ($tags) {
            $data['data'] = [];
            foreach ($tags as $key => $tag) {
                $data['data'][$key] = $tag->attributes();
                $data['data'][$key]['category'] = $tag->category->name;
                $data['data'][$key]['user'] = $tag->user->email;
                $data['data'][$key]['date'] = date('d-m-Y H:i:s', strtotime($tag->created_at));
                $data['data'][$key]['slug'] = Tool::VNConvert($tag->title);
                if (!file_exists('../'. $data['data'][$key]['image'])) {
                    $data['data'][$key]['image'] = 'uploads/no_image.jpg';
                }
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
            return ceil(self::count() / $perPage);
        }
    }

    public static function getOffset($page = 1, $perPage = 10) {
        return ($page - 1)*$perPage;
    }
}
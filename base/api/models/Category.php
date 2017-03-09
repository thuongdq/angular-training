<?php

class Category extends ActiveRecord\Model {
	static $has_many = [
		['posts']
	];

	public static function getCategory($category = '') {
		if (is_numeric($category)) {
			$category = self::find($category)->attributes();
		} else {
			$category = self::find('first', ['conditions' => ['name = ?', $category]]);
			$category = $category ? $category->attributes() : false;
		}

		if ($category) {			
			return $category;
		}
		return false;
	}

	public static function getCategories($perPage = 10, $range = 2) {
		$page = Input::get('page');
		if (!$page || !is_numeric($page) || $page < 0) {
			$page = 1;
		}
		if ($perPage) {
			$categories = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage)]);
		} else {
			$categories = self::find('all');
		}

		if ($categories) {
			$data['data'] = [];
			foreach ($categories as $key => $category) {
				$data['data'][$key] = $category->attributes();
				$data['data'][$key]['index'] = ($page - 1) * $perPage + $key;
			}
			if ($perPage && ($totalPages = self::getPages($perPage)) > 1) { 
				$data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
					return sprintf('categories/page/%s', $currentPage);
				});
			}
			return $data;
		}		
		return false;
	}

	public static function getPages($perPage = 10) {
		return ceil(self::count() / $perPage);
	}

	public static function getOffset($page = 1, $perPage = 10) {
		return ($page - 1)*$perPage;
	}
}
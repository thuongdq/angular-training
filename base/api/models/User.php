<?php
class User extends ActiveRecord\Model {
	static $has_many = [
		['posts']
	];	
	
	public static function getUser($user = '', $showPassword = false) {
		if (is_numeric($user)) {
			$user = self::find('first', ['conditions' => ['id = ?', $user]]);
			$user = $user !== null ? $user->attributes() : false;
		} else {
			$user = self::find('first', ['conditions' => ['email = ?', $user]]);
			$user = $user !== null ? $user->attributes() : false;
		}

		if ($user) {
			if (!$showPassword) {
				unset($user['password']);
			}
			$user['level'] = (string)$user['level'];
			return $user;
		}
		return false;
	}

	public static function isLogin() {
		return Session::get('user');
	}

	public static function getUsers($showPassword = false, $perPage = 10, $range = 2) {
		$page = Input::get('page');
		if (!$page || !is_numeric($page) || $page < 0) {
			$page = 1;
		}
		if ($perPage) {
			$users = self::find('all', ['limit' => $perPage, 'offset' => self::getOffset($page, $perPage)]);
		} else {
			$users = self::find('all');
		}

		if ($users) {
			$data['data'] = [];
			foreach ($users as $key => $user) {				
				$data['data'][$key] = $user->attributes();
				$data['data'][$key]['index'] = ($page - 1) * $perPage + $key;
				if (!$showPassword) {
					unset($data['data'][$key]['password']);
				}
			}
			if ($perPage && ($totalPages = self::getPages($perPage)) > 1) { 
				$data['pagination'] = Tool::pagination($totalPages, $perPage, $page, $range, function($currentPage) {
					return sprintf('users/page/%s', $currentPage);
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

	public static function deleteUser($user) {
		if (is_numeric($user)) {
			$user = self::find('first', ['conditions' => ['id = ?', $user]]);
		} else {
			$user = self::find('first', ['conditions' => ['email = ?', $user]]);
		}

		if ($user) {
			$user->delete();
			return $user;
		}
		return false;
		
	}
}
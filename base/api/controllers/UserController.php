<?php

class UserController {
	public function index() {
		$users = User::getUsers(false, 3);
		Response::update('users', $users);
		Response::printData();
	}

	public function create() {
		$email = Input::get('email');
		$password = Input::get('password');
		$confirm_password = Input::get('confirm_password');
		$level = intval(Input::get('level'));

		if (!$email) {			
			Response::warning('Email không được bỏ trống');
		}

		if ($email && User::getUser($email)) {			
			Response::warning('Email đã trùng, vui lòng chọn Email khác');
		}

		if (!$password || !$confirm_password || $password != $confirm_password) {			
			Response::warning('Mật khẩu không trùng hoặc bỏ trống');
		}

		if (!$level || $level <= 0 || $level > 2) {
			Response::warning('Vui lòng chọn quyền hạn');
		}

		if (Response::isValid()) {
			$user = User::create([
				'email' => $email,
				'password' => App::encryptPassword($password),
				'level' => $level
			]);						
			Response::messages("Tạo người dùng $email thành công");
		}
		Response::printData();
	}

	public function update($id) {
		$password = Input::get('password');
		$confirm_password = Input::get('confirm_password');
		$level = intval(Input::get('level'));

		$current_user = User::find($id);

		if ($password && $password != $confirm_password) {			
			Response::warning('Mật khẩu không trùng hoặc bỏ trống');
		}

		if (!$level || $level <= 0 || $level > 2) {
			Response::warning('Vui lòng chọn quyền hạn');
		}

		if (Response::isValid()) {
			if ($password && $confirm_password) {
				$current_user->password = App::encryptPassword($password);
			}	
			$current_user->level = $level;		
			$current_user->save();			
			Response::messages("Cập nhật người dùng \"$current_user->email\" thành công");
		}
		Response::printData();
	}

	public function show($id) {
		$user = User::getUser($id);
		if ($user) {
			Response::update('user', $user);
		}
		Response::printData();
	}

	public function delete($id) {
		if (Input::get()) {
			$user = User::deleteUser($id);
			if ($user) {
				Response::messages("Xóa người dùng \"$user->email\" thành công");				
			} else {
				Response::warning("Không tìm thấy người dùng này để xóa");
			}
		}
		Response::printData();
	}
}
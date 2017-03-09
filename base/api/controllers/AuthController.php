<?php

class AuthController {
    public function login() {
        $email = Input::get('email');
        $password = Input::get('password');
        
        if (!$email) {
            Response::warning('Vui lòng nhập Email');
        }
        
        if (!$password) {
            Response::warning('Vui lòng nhập Password') ;
        }
        
        if ($email && $password && Response::isValid()) {
            $user = User::getUser($email, true);
            if ($user && App::verifyPassword($password, $user['password'])) {
                if ($user['level'] == 1) {
                    unset($user['password']);
                    Session::update('user', [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'level' => $user['level']
                    ]);
                    Response::updateData('user',  [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'level' => $user['level']
                    ]);
                    Response::update('isLogin', true);
                    Response::messages('Đăng nhập thành công');
                } else {
                    Response::update('isLogin', false);
                    Response::warning('Người dùng không đủ quyền hạn.');
                }
            } else {
                Response::update('isLogin', false);
                Response::warning('Thông tin người dùng không chính xác');
            }
        }
        
        Response::printData();
    }
    
    public function logout() {
        Session::destroy();
        Response::update('isLogin', false);
        Response::printData();
    }
    
    public function isLogin() {
        Response::update('isLogin', true);
        Response::updateData('user', Session::get('user'));
        if (!User::isLogin()) {
            Response::update('isLogin', false);
            Response::warning('Bạn không được phép truy cập');
        }
        Response::printData();
    }
    
    public function register() {
        $email = Input::get('email');
        $password = Input::get('password');
        $confirm_password = Input::get('confirm_password');
        
        if (!$email) {
            Response::warning('Email không được bỏ trống');
        }
        
        if ($email && User::getUser($email)) {
            Response::warning('Email đã trùng, vui lòng chọn Email khác');
        }
        
        if (!$password || !$confirm_password || $password != $confirm_password) {
            Response::warning('Mật khẩu không trùng hoặc bỏ trống');
        }
        
        if (Response::isValid()) {
            $user = User::create([
            'email' => $email,
            'password' => App::encryptPassword($password),
            'level' => 2
            ]);
            Session::update('user', [
            'id' => $user->id,
            'email' => $user->email,
            'level' => $user->level
            ]);
            Response::updateData('user', User::getUser($user->id));
            Response::messages("$email đã đăng ký thành công");
        }
        Response::printData();
    }
}
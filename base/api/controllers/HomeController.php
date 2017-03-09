<?php

class HomeController {
	public function index() {
		$data['posts'] = Post::getPosts(10, 2, false);
		echo App::toJson($data);
	}

	public function show($id) {		
		$post = Post::getPost($id);
		$data['errors'] = [];
		if ($post) {
			$data['post'] = $post;
		} else {
			$data['errors'][] = 'Không tìm thấy bài viết';
		}
		echo App::toJson($data);
	}

	public function category($id) {
		$posts = Post::getPostsByCategory($id);
		$data['errors'] = [];
		if ($posts) {
			$data['posts'] = $posts;
		} else {
			$data['errors'][] = 'Không tìm thấy bài viết';
		}
		echo App::toJson($data);
	}

	public function getCategories() {
		$data['categories'] = Category::getCategories(false);
		echo App::toJson($data);
	}
}
<?php

class CategoryController {
	public function index() {		
		Response::update('categories', Category::getCategories(3));
		Response::printData();
	}

	public function create() {		
		$name = Input::get('name');
		$order = Input::get('order');

		if (!$name) {			
			Response::warning('Chuyên mục không được bỏ trống');
		}


		if (!$order || !is_numeric($order)) {
			$order = 0;
		}

		if ($name && Category::getCategory($name)) {			
			Response::warning('Chuyên mục đã trùng, vui lòng chọn chuyên mục khác');
		}

		if (Response::isValid()) {
			Category::create([
				'name' => $name,				
				'order' => $order
			]);			
			Response::messages("Tạo chuyên mục $name thành công");
		}
		Response::printData();
	}

	public function update($id) {
		$name = Input::get('name');
		$order = Input::get('order');

		if (!$name) {			
			Response::warning('Chuyên mục không được bỏ trống');
		}

		if (!$order || !is_numeric($order)) {
			$order = 0;
		}

		$current_category = Category::find($id);
		$another_category = Category::getCategory($name);
		if ($name && $another_category && $another_category['id'] != $id) {			
			Response::warning('Chuyên mục đã trùng, vui lòng chọn chuyên mục khác');
		}

		if (Response::isValid()) {
			$current_category->name = $name;		
			$current_category->order = $order;
			$current_category->save();			
			Response::messages("Cập nhật chuyên mục $name thành công");
		}
		Response::printData();
	}

	public function show($id) {		
		$category =	Category::getCategory($id);
		if ($category) {
			Response::update('category', $category);			
		} else {			
			Response::warning('Không tìm thấy chuyên mục');
		}		
		Response::printData();
	}

	public function delete($id) {
		if (Input::get()) {
			$category = Category::find('first', ['conditions' => ['id = ?', $id]]);						
			if ($category) {
				$category->delete();
				Response::messages("Xóa thành công chuyên mục $category->name");
			} else {
				Response::warning('Không tìm thấy chuyên mục');
			}
		}
		Response::printData();
	}
}
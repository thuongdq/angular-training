<?php

class TagController {
	public function index() {		
		Response::updateData('tags', Tag::getTags(10));
		Response::printData();
	}

	public function create() {		
		$name = Input::get('name');
		$order = Input::get('order');

		if (!$name) {
			Response::warning('Thẻ không được bỏ trống');
		}


		if (!$order || !is_numeric($order)) {
			$order = 0;
		}

		if ($name && Tag::getTag($name)) {
			Response::warning('Thẻ đã trùng, vui lòng chọn Thẻ khác');
		}

		if (!$data['errors']) {
			Tag::create([
				'name' => $name,				
				'order' => $order
			]);			
			Response::messages("Tạo thẻ $name thành công");
		}
		Response::printData();
	}

	public function update($id) {
		$name = Input::get('name');
		$order = Input::get('order');

		if (!$name) {			
			Response::warning('Thẻ không được bỏ trống');
		}

		if (!$order || !is_numeric($order)) {
			$order = 0;
		}

		$current_tag = Tag::find($id);
		$another_tag = Tag::getTag($name);
		if ($name && $another_tag && $another_tag['id'] != $id) {			
			Response::warning('Thẻ đã trùng, vui lòng chọn Thẻ khác');
		}

		if (Response::isValid()) {
			$current_tag->name = $name;		
			$current_tag->order = $order;
			$current_tag->save();			
			Response::messages("Cập nhật thẻ $name thành công");
		}
		Response::printData();
	}

	public function show($id) {
		$tag = Tag::getTag($id);
		if ($tag) {
			$data['tag'] = $tag;
			Response::updateData('tag', $tag);
		} else {			
			Response::warning('Không tìm thấy thẻ');
		}
		Response::printData();
	}

	public function delete($id) {
		if (Input::get()) {
			$tag = Tag::find($id);			
			Response::messages("Xóa thành công thẻ $tag->name");
			$tag->delete();
		}
		Response::printData();
	}
}
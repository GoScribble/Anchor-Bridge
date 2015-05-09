<?php

Route::collection(array(), function() {

	Route::post('scribbleapi', function() {
		
	        if($user = User::where('username', '=', $_SERVER['PHP_AUTH_USER'])->where('status', '=', 'active')->fetch()) {
			// found a valid user now check the password
			if(Hash::check($_SERVER['PHP_AUTH_PW'], $user->password)) {
				$userid = $user->id;
			}
		}
		
		//Check if authentication failed
		if (empty($userid)) {
		    return json_encode(["status" => "fail", "message" => "Authentication failed, check the credentials in your Scribble 'Config/config.php' file."]);
		}
	        
		$input = [
		    'title'        => $_POST["post_title"],
		    'slug'         => str_replace(" ", "-", strtolower($_POST["post_title"])),
		    'description'  => '',
		    'created'      => date("Y-m-d H:i:s"),
		    'html'         => $_POST["post_content"],
		    'css'          => '',
		    'js'           => '',
		    'category'     => $_POST["cat_id"],
		    'status'       => 'published',
		    'comments'     => 0,
		    'author'       => $userid
		];

		// if there is no slug try and create one from the title
		if(empty($input['slug'])) {
			$input['slug'] = $input['title'];
		}

		// convert to ascii
		$input['slug'] = slug($input['slug']);

		// encode title
		$input['title'] = e($input['title'], ENT_COMPAT);

		$validator = new Validator($input);

		$validator->add('duplicate', function($str) {
			return Post::where('slug', '=', $str)->count() == 0;
		});

		$validator->check('title')
			->is_max(3, __('posts.title_missing'));

		$validator->check('slug')
			->is_max(3, __('posts.slug_missing'))
			->is_duplicate(__('posts.slug_duplicate'))
			->not_regex('#^[0-9_-]+$#', __('posts.slug_invalid'));

		if($errors = $validator->errors()) {
			Input::flash();

			Notify::error($errors);

			return Response::redirect('admin/posts/add');
		}

		if(empty($input['created'])) {
			$input['created'] = Date::mysql('now');
		}

		if(is_null($input['comments'])) {
			$input['comments'] = 0;
		}

		if(empty($input['html'])) {
			$input['status'] = 'draft';
		}

		$post = Post::create($input);

		Extend::process('post', $post->id);

		Notify::success(__('posts.created'));
	        
		return json_encode(["status" => "ok"]);
	});

});
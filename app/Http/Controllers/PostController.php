<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function showCreateForm()
    {
        return view('create-post', ['title' => "Create New Post"]);   
    }

    public function storeNewPost(Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        return redirect("/post/{$newPost->id}")->with('success','new post created successfully!');
    }

    public function viewSinglePost(Post $post)
    {
        $post['body'] = strip_tags(Str::markdown($post->body), '<p><ul><ol><li><strong><br><h1><h2><h3>');
        return view('single-post', ['post' => $post]);
    }

    public function delete(Post $post)
    {
        // if (auth()->user()->cannot('delete', $post)) {
        //     return 'you cannot do that';
        // }

        $post->delete();
        return redirect('/profile/'.auth()->user()->username)->with('success', 'post deleted!');
    }

    public function showEditForm(Post $post)
    {
        return view('edit-post',[
            'post' => $post,
            'title' => "Editing: ".$post->title
        ]);
    }

    public function actuallyUpdate(Post $post, Request $request)
    {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        return redirect('/post/'.$post->id)->with('success', 'post updated!');
    }

    public function search($term)
    {
        $result = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->where('posts.title', 'LIKE', "%".$term."%")
            ->orWhere('posts.body', 'LIKE', "%".$term."%")
            ->select('posts.id', 'posts.title', 'posts.body', 'posts.created_at', 'users.avatar', 'users.username')
            ->get();

        foreach ($result as $data) {
            // If "avatar" is null, set it to a default value
            if (is_null($data->avatar)) {
                $data->avatar = '/fallback-avatar.jpg';
            }else {
                $data->avatar = '/storage/avatars/'.$data->avatar;
            }
        }
        return $result;
    }
}

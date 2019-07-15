<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Post;
use App\Zan;
use App\PostTopic;
use App\Topic;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // 列表
    public function index()
    {
        $posts = Post::withCount(['comments', 'zans','reposts'])->orderBy('created_at', 'desc')->orderBy('zans_count', 'desc')->paginate(2);
        $posts->load('user');
        return view("post/index", compact('posts'));
    }

    // 详情页面
    public function show(Post $post)
    {
        $post->load('comments');
        return view("post/show", compact('post'));
    }

    // 创建页面
    public function create()
    {
        return view("post/create");
    }

    // 创建逻辑
    public function store(Request $request)
    {
        // 验证
        $this->validate(request(),[
            'title' => 'required|string|max:100|min:0',
        ]);
        // 逻辑
        $user = \Auth::user();
        $post = new Post;
        if ($request->file('avatar')) {
            $path = $request->file('avatar')->storePublicly($user->id);
            $post->avatar = "/storage/" . $path;
        }
        $post->title = request('title');
        $post->user_id = $user->id;
        $post->assumed_name = $user->assumed_name;
        $post->level_id = 1;
        $post->save();
//        对新建的推送写入原始postid(id)
        $newpost = Post::where('id', $post->id)->first();
        $newpost->original_post_id = $newpost->id;
        $newpost->save();
//       生成话题
        if(request('topic_name')){
            if (Topic::where('name', request('topic_name'))->count() > 0) {
                $topic = Topic::where('name', request('topic_name'))->first();
                $posttopic = new PostTopic;
                $posttopic->post_id = $post->id;
                $posttopic->topic_id = $topic->id;
                $posttopic->save();
            }else{
                $topic = new Topic;
                $topic->name = request('topic_name');
                $topic->save();
                $posttopic = new PostTopic;
                $posttopic->post_id = $post->id;
                $posttopic->topic_id = $topic->id;
                $posttopic->save();
            }
        }

        // 渲染
        return redirect("/posts");
    }
    // 转发逻辑
    public function repost(Request $request)
    {
        // 验证
        $this->validate(request(),[
            'title' => 'required|string|max:100|min:0',
            'forward_post_id' => 'required',
            'original_post_id' => 'required',
            'level_id' => 'required',
        ]);
        // 逻辑
        $user = \Auth::user();
        $post = new Post;
        if ($request->file('avatar')) {
            $path = $request->file('avatar')->storePublicly($user->id);
            $post->avatar = "/storage/" . $path;
        }
        $post->title = request('title');
        $post->user_id = $user->id;
        $post->assumed_name = $user->assumed_name;
        $post->forward_post_id = request('forward_post_id');
        $post->level_id = request('level_id') + 1;
        $post->original_post_id = request('original_post_id');
        $post->save();

//        生成话题
        if(request('topic_name')){
            if (Topic::where('name', request('topic_name'))->count() > 0) {
                $topic = Topic::where('name', request('topic_name'))->first();
                $posttopic = new PostTopic;
                $posttopic->post_id = $post->id;
                $posttopic->topic_id = $topic->id;
                $posttopic->save();
            }else{
                $topic = new Topic;
                $topic->name = request('topic_name');
                $topic->save();
                $posttopic = new PostTopic;
                $posttopic->post_id = $post->id;
                $posttopic->topic_id = $topic->id;
                $posttopic->save();
            }
        }

        // 渲染
        return redirect("/posts");
    }
    // 编辑页面
    public function edit(Post $post)
    {
        return view('post/edit', compact('post'));
    }

    // 编辑逻辑
    public function update(Post $post)
    {
        // 验证
        $this->validate(request(),[
            'title' => 'required|string|max:100|min:5',
            'content' => 'required|string|min:10',
        ]);

        $this->authorize('update', $post);

        // 逻辑
        $post->title = request('title');
        $post->content = request('content');
        $post->save();

        // 渲染
        return redirect("/posts/{$post->id}");
    }

    // 删除逻辑
    public function delete(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect("/posts");
    }

    // 上传图片

    /**
     * @param Request $request
     * @return false|string
     */
    public function imageUpload(Request $request)
    {
        $path = $request->file('wangEditorH5File')->storePublicly(md5(time()));
        $data = asset('storage/' . $path);
        echo json_encode(array(
            "error" => 0,
            "data" => $data,
        ));
    }

    // 提交评论
    public function comment(Post $post)
    {
        $this->validate(request(),[
            'content' => 'required|min:3',
        ]);

        // 逻辑
        $comment = new Comment();
        $comment->user_id = \Auth::id();
        $comment->content = request('content');
        $post->comments()->save($comment);

        // 渲染
        return back();
    }

    // 赞
    public function zan(Post $post)
    {
        $param = [
            'user_id' => \Auth::id(),
            'post_id' => $post->id,
        ];

        Zan::firstOrCreate($param);
        $postzans = Post::withCount(['comments', 'zans','topics','reposts'])->find($post->id);
        return [
            'error' => 0,
            'zans' => $postzans->zans_count,
            'msg' => ''
        ];
    }

    // 取消赞
    public function unzan(Post $post)
    {
        $post->zan(\Auth::id())->delete();
        $postzans = Post::withCount(['comments', 'zans','topics','reposts'])->find($post->id);
        return [
            'error' => 0,
            'zans' => $postzans->zans_count,
            'msg' => ''
        ];
    }

    // 搜索结果页
    public function search()
    {
        // 验证
        $this->validate(request(),[
            'query' => 'required'
        ]);
        // 逻辑
        $query = request('query');
        $posts = \App\Post::search($query)->paginate(10);

        // 渲染
        return view("post/search", compact('posts', 'query'));
    }
}

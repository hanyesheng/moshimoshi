@extends("layout.main")

@section("content")
    <div class="col-sm-8">
        <blockquote>
            <p><img src="{{$user->avatar}}" alt="" class="img-rounded" style="border-radius:500px; height: 40px"> {{$user->name}}
            </p>


            <footer>关注：{{$user->stars_count}}｜粉丝：{{$user->fans_count}}｜文章：{{$user->posts_count}}</footer>
            @include('user.badges.like', ['target_user' => $user])
        </blockquote>
        <div class="col-sm-12 blog-main">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">文章</a></li>
                    <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">关注</a></li>
                    <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">粉丝</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        @foreach($countposts as $post)
                        <div class="blog-post" style="margin-top: 30px">
                            <p class=""><a href="/user/{{$post->user->id}}">{{$post->user->name}}</a> {{$post->created_at->diffForHumans()}}</p>
                            <p class=""><a href="/posts/{{$post->id}}" >{{$post->title}}</a></p>
                            <p> </p>
                            <p class="blog-post-meta">赞 {{$post->zans_count}}  | 评论 {{$post->comments_count}}</p>
                        </div>
                        @endforeach
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        @foreach($susers as $user)
                        <div class="blog-post" style="margin-top: 30px">
                            <p class=""><a href="/user/{{$user->id}}">{{$user->name}}</a></p>
                            <p class="">关注：{{$user->stars_count}} | 粉丝：{{$user->fans_count}}｜ 文章：{{$user->posts_count}}</p>

                            @include('user.badges.like', ['target_user' => $user])

                        </div>
                        @endforeach
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        @foreach($fusers as $user)
                            <div class="blog-post" style="margin-top: 30px">
                                <p class=""><a href="/user/{{$user->id}}">{{$user->name}}</a></p>
                                <p class="">关注：{{$user->stars_count}} | 粉丝：{{$user->fans_count}}｜ 文章：{{$user->posts_count}}</p>

                                @include('user.badges.like', ['target_user' => $user])

                            </div>
                        @endforeach
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
@endsection
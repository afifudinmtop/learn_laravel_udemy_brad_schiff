<x-layout>
    <div class="container py-md-5 container--narrow">
        @if (count($list_post))
            <h2 class="mb-4 text-center">The Latest From Those You Follow</h2>
            <div class="list-group">
                @foreach ($list_post as $post_item)
                    <a href="/post/{{ $post_item->id }}" class="list-group-item list-group-item-action">
                        <img class="avatar-tiny" src="{{ $post_item->avatar }}" />
                        <strong>{{ $post_item->title }}</strong>
                        <span class="text-muted small">by {{ $post_item->username }} on {{ $post_item->created_at }}</span>
                    </a>
                @endforeach
            </div>
            <div class="mt-4">
                {{$list_post->links()}}
            </div>
            
        @else
            <div class="text-center">
                <h2>Hello <strong>{{ auth()->user()->username }}</strong>, your feed is empty.</h2>
                <p class="lead text-muted">Your feed displays the latest posts from the people you follow. If you don&rsquo;t have any friends to follow that&rsquo;s okay; you can use the &ldquo;Search&rdquo; feature in the top menu bar to find content written by people with similar interests and then follow them.</p>
            </div>
        @endif
    </div>
</x-layout>
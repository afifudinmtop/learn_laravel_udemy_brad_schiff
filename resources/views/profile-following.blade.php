<x-layout :docTitle="$title">
    <div class="container py-md-5 container--narrow">
        <h2>
          <img class="avatar-small" src="{{ $user->avatar }}" /> {{ $user->username }}

          @auth
              
            {{-- user can't follow their-self --}}
            @if (auth()->user()->username === $user->username)
              <a href="/manage-avatar" class="btn btn-secondary btn-sm">Manage Avatar</a>
            @else

              {{-- if already following --}}
              @if ($followCheck)
              <form class="ml-2 d-inline" action="/remove-follow/{{$user->username}}" method="POST">
                @csrf
                <button class="btn btn-danger btn-sm">
                  Stop Following <i class="fas fa-user-times"></i>
                </button>
              </form>

              {{-- if not following --}}
              @else
                <form class="ml-2 d-inline" action="/create-follow/{{$user->username}}" method="POST">
                  @csrf
                  <button class="btn btn-primary btn-sm">
                    Follow <i class="fas fa-user-plus"></i>
                  </button>
                </form>
              @endif
              
            @endif
             
          @endauth
        </h2>
  
        <div class="profile-nav nav nav-tabs pt-2 mb-4">
          <a href="/profile/{{$user->username}}/" class="profile-nav-link nav-item nav-link">Posts: {{ $post_count }}</a>
          <a href="/profile/{{$user->username}}/followers/" class="profile-nav-link nav-item nav-link">Followers: {{ $followers_count }}</a>
          <a href="/profile/{{$user->username}}/following/" class="profile-nav-link nav-item nav-link active">Following: {{ $following_count }}</a>
        </div>
  
        <div class="list-group">
          @foreach ($list_following as $x)
            <a href="/profile/{{ $x->username }}" class="list-group-item list-group-item-action">
              @if ($x->avatar)
                <img class="avatar-tiny" src="/storage/avatars/{{ $x->avatar }}" />
              @else
                <img class="avatar-tiny" src="/fallback-avatar.jpg" />
              @endif
              
              <strong>{{ $x->username }}</strong>
            </a>
          @endforeach
        </div>
    </div>
</x-layout>
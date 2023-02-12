<div class="favorite-list-item">
    <div data-id="{{ $user->id }}" data-action="0" class="avatar av-m"
         style="background-image: url('{{ App\Facades\ChatifyMessenger::getUserWithAvatar($user)->avatar }}');">
    </div>

    @php
        $displayName = $user->company_name == "" ? $user->name : $user->company_name;
    @endphp

    <p> {{ strlen($displayName) > 5 ? trim(substr($displayName, 0, 6)).'..' : $displayName }} </p>
</div>

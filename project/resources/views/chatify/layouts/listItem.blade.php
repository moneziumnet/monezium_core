{{-- -------------------- Saved Messages -------------------- --}}
@if($get == 'saved')
    <table class="messenger-list-item m-li-divider" data-contact="{{ Auth::user()->id }}">
        <tr data-action="0">
            {{-- Avatar side --}}
            <td>
                <div class="avatar av-m"
                     style="background-color: #d9efff; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #68a5ff;"></span>
                </div>
            </td>
            {{-- center side --}}
            <td>
                <p data-id="{{ Auth::user()->id }}" data-type="user">{{__("Saved Messages")}} <span>{{__("You")}}</span></p>
                <span>{{__("Save messages secretly")}}</span>
            </td>
        </tr>
    </table>
@endif

{{-- -------------------- All users/group list -------------------- --}}
@if($get == 'users')
    <table class="messenger-list-item" data-contact="{{ $user->id }}">
        <tr data-action="0">
            {{-- Avatar side --}}
            <td style="position: relative">
                <div class="avatar av-m"
                     style="background-image: url('{{ $user->avatar }}');">
                </div>
                @if($user->active_status)
                    <span class="activeStatus"></span>
                @endif
            </td>
            {{-- center side --}}
            <td>
                @php
                    $displayName = $user->company_name == "" ? $user->name : $user->company_name;
                @endphp
                <p data-id="{{ $user->id }}" data-type="user">
                    {{ strlen($displayName) > 20 ? trim(substr($displayName, 0 ,20)).'..' : $displayName }}
                    <span>{{ $lastMessage->created_at->diffForHumans() }}</span></p>
                <span>
            {{-- Last Message user indicator --}}
                    {!!
                        $lastMessage->from_id == Auth::user()->id
                        ? '<span class="lastMessageIndicator">You :</span>'
                        : ''
                    !!}
                    {{-- Last message body --}}
                    @if($lastMessage->attachment == null)
                        {!!
                            strlen($lastMessage->body) > 30
                            ? trim(substr($lastMessage->body, 0, 30)).'..'
                            : $lastMessage->body
                        !!}
                    @else
                        <span class="fas fa-file"></span> {{__("Attachment")}}
                    @endif
        </span>
                {{-- New messages counter --}}
                {!! $unseenCounter > 0 ? "<b>".$unseenCounter."</b>" : '' !!}
            </td>

        </tr>
    </table>
@endif

{{-- -------------------- Search Item -------------------- --}}
@if($get == 'search_item')
    <table class="messenger-list-item" data-contact="{{ $user->id }}">
        <tr data-action="0">
            {{-- Avatar side --}}
            <td>
                <div class="avatar av-m"
                     style="background-image: url('{{ $user->avatar }}');">
                </div>
            </td>
            {{-- center side --}}
            @php
                $displayName = $user->company_name == "" ? $user->name : $user->company_name;
            @endphp
            <td>
                <p data-id="{{ $user->id }}" data-type="user">
                {{ strlen($displayName) > 20 ? trim(substr($displayName, 0, 20)).'..' : $displayName }}
            </td>

        </tr>
    </table>
@endif

{{-- -------------------- Shared photos Item -------------------- --}}
@if($get == 'sharedPhoto')
    <div class="shared-photo chat-image" style="background-image: url('{{ $image }}')"></div>
@endif



@extends('layouts.admin')

@section('title', 'スタッフ一覧 - 管理者')

@section('content')
<div class="page-header">
    <h1 class="page-header__title">スタッフ一覧</h1>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><a href="{{ route('admin.staff.attendance', $user->id) }}" class="table__link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $staff->links() }}
</div>
@endsection

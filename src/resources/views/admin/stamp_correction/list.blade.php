@extends('layouts.admin')

@section('title', '修正申請一覧 - 管理者')

@section('content')
<div class="page-header">
    <h1 class="page-header__title">申請一覧</h1>
</div>

<div class="tabs">
    <a href="{{ route('stamp_correction.list', ['tab' => 'pending']) }}"
       class="tabs__item {{ $tab === 'pending' ? 'tabs__item--active' : '' }}">承認待ち</a>
    <a href="{{ route('stamp_correction.list', ['tab' => 'approved']) }}"
       class="tabs__item {{ $tab === 'approved' ? 'tabs__item--active' : '' }}">承認済み</a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日付</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($corrections as $correction)
            <tr>
                <td>
                    @if($correction->isPending())
                    <span class="badge badge--pending">承認待ち</span>
                    @else
                    <span class="badge badge--approved">承認済み</span>
                    @endif
                </td>
                <td>{{ $correction->user->name }}</td>
                <td>{{ $correction->request_date->format('Y/m/d') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($correction->reason, 30) }}</td>
                <td>{{ $correction->created_at->format('Y/m/d H:i') }}</td>
                <td><a href="{{ route('admin.stamp_correction.show', $correction->id) }}" class="table__link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $corrections->appends(['tab' => $tab])->links() }}
</div>
@endsection

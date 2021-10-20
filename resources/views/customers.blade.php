@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Customers</h1>
@stop

@section('content')
    <table class="table table-bordered" id="customers-table">
        <thead>
            <tr>
                <th>Id</th>
                <th>FIO</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
    </table>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
@stop

@section('js')
    <!-- DataTables -->
    <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <script>
    $(function() {
        $('#customers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{!! route('datatables.products') !!}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'fio', name: 'fio' },
                { data: 'phone', name: 'phone' },
                { data: 'email', name: 'email' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' }
            ]
        });
    });
    </script>
@stop

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <form class="form-horizontal" method="POST" role="form" action="{{ url('import') }}">
                <!-- Wecenter -->
                <div class="panel panel-default">
                    <div class="panel-heading">Wecenter数据库</div>

                    <div class="panel-body">
                            <div class="form-group">
                                <label class="col-md-4 control-label">数据库主机</label>

                                <div class="col-md-6 input-group">
                                    <input type="text" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">数据库帐户</label>

                                <div class="col-md-6 input-group">
                                    <input type="text" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">数据库密码</label>

                                <div class="col-md-6 input-group">
                                    <input type="password" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">数据库名称</label>

                                <div class="col-md-6 input-group">
                                    <input type="text" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">数据表前缀</label>

                                <div class="col-md-6 input-group">
                                    <input type="text" class="form-control">
                                </div>
                            </div>


                    </div>
                </div>

                <!-- ucenter-->
                <div class="panel panel-default">
                    <div class="panel-heading">Ucenter数据库</div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-md-4 control-label">数据库主机</label>

                            <div class="col-md-6 input-group">
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">数据库帐户</label>

                            <div class="col-md-6 input-group">
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">数据库密码</label>

                            <div class="col-md-6 input-group">
                                <input type="password" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">数据库名称</label>

                            <div class="col-md-6 input-group">
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label">数据表前缀</label>

                            <div class="col-md-6 input-group">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            开始导入
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

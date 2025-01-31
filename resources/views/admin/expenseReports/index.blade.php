@extends('layouts.admin')
@section('content')
    <div class="row">
        <div class="col">
            <h3 class="page-title">{{ trans('cruds.expenseReport.reports.title') }}</h3>

            <form method="get">
                <div class="row">
                    <div class="col-3 form-group">
                        <label class="control-label" for="y">{{ trans('global.year') }}</label>
                        <select name="y" id="y" class="form-control">
                            @foreach (array_combine(range(date('Y'), 1900), range(date('Y'), 1900)) as $year)
                                <option value="{{ $year }}" @if ($year === old('y', Request::get('y', date('Y')))) selected @endif>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3 form-group">
                        <label class="control-label" for="m">{{ trans('global.month') }}</label>
                        <select name="m" for="m" class="form-control">
                            @foreach (cal_info(0)['months'] as $month)
                                <option value="{{ $month }}" @if ($month === old('m', Request::get('m', date('m')))) selected @endif>
                                    {{ $month }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="control-label">&nbsp;</label><br>
                        <button class="btn btn-primary" type="submit">{{ trans('global.filterDate') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            {{ trans('cruds.expenseReport.reports.incomeReport') }}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>{{ trans('cruds.expenseReport.reports.income') }}</th>
                            <td>{{ number_format($incomesTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('cruds.expenseReport.reports.expense') }}</th>
                            <td>{{ number_format($expensesTotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('cruds.expenseReport.reports.profit') }}</th>
                            <td>{{ number_format($profit, 2) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>{{ trans('cruds.expenseReport.reports.incomeByCategory') }}</th>
                            <th>{{ number_format($incomesTotal, 2) }}</th>
                        </tr>
                        @foreach ($incomesSummary as $inc)
                            <tr>
                                <th>{{ $inc['name'] }}</th>
                                <td>{{ number_format($inc['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="col">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>{{ trans('cruds.expenseReport.reports.expenseByCategory') }}</th>
                            <th>{{ number_format($expensesTotal, 2) }}</th>
                        </tr>
                        @foreach ($expensesSummary as $inc)
                            <tr>
                                <th>{{ $inc['name'] }}</th>
                                <td>{{ number_format($inc['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>



        </div>
    </div>
    <div id="graph">Graph</div>

@endsection

@section('scripts')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.4.5/jquery-ui-timepicker-addon.min.js">
    </script>
    <script src="https://code.highcharts.com/highcharts.js"></script>



    <script>
       
        $(document).ready(function() {
            $.ajax({
                url: '/admin/chart', // Adjust the URL as necessary
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Render Highcharts chart
                    Highcharts.chart('graph', {
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: 'Monthly Income and Expenses'
                        },
                        xAxis: {
                            categories: data.months, // Use month names from the response
                            title: {
                                text: 'Months'
                            }
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Amount',
                                align: 'high'
                            },
                            labels: {
                                overflow: 'justify'
                            }
                        },
                        tooltip: {
                            valueSuffix: ' units'
                        },
                        series: [{
                            name: 'Income',
                            data: data.income // Income data from the response
                        }, {
                            name: 'Expenses',
                            data: data.expenses // Expenses data from the response
                        }]
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        });
    </script>
@stop

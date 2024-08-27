<!-- index.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Counts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    <title>Date Range Picker Example</title>
    <!-- Include CSS for Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <!-- Include Date Range Picker JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
</head>
<body>
    <h1>Lead Name Count</h1>

    <form id="dateFilterForm">
        <input type="text" id="hidden-input" style="display: none;" />
        <!-- Date Range Picker Display Div -->
        <div id="daterange" style="border: 1px solid #ccc; padding: 10px; width: 250px; cursor: pointer;">
            <span>Select Date Range</span>
        </div>
    </form>

    <table id="leadTable" border="1">
        <thead>
            <tr>
                <th>Lead Name</th>
                <th>Total Lead Name Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leadCounts as $leadCount)
                <tr>
                    <td>{{ $leadCount->lead_name }}</td>
                    <td>{{ $leadCount->total_lead_name_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            // Initialize the date range picker with predefined ranges
            $('#daterange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                },
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'This Month': [moment().startOf('month'), moment().endOf('month')]
                }
            });

            // Update hidden input and trigger filter on apply
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                $('#hidden-input').val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                filterData();
            });

            // Clear input and table data on cancel
            $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                $('#hidden-input').val('');
                $('#leadTable tbody').empty();
            });

            function filterData() {
                var dateRange = $('#hidden-input').val();
                var dates = dateRange.split(' - ');

                var dateFrom = dates[0];
                var dateTo = dates[1];

                $.ajax({
                    url: "{{ route('leads.index') }}", // Ensure this URL is correct
                    method: "GET",
                    data: {
                        date_from: dateFrom,
                        date_to: dateTo
                    },
                    success: function(data) {
                        var rows = '';
                        $.each(data, function(index, lead) {
                            rows += '<tr>';
                            rows += '<td>' + lead.lead_name + '</td>';
                            rows += '<td>' + lead.total_lead_name_count + '</td>';
                            rows += '</tr>';
                        });
                        $('#leadTable tbody').html(rows);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            }
        });
    </script>
    
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <h1>Date Range Picker Example</h1>

    <!-- Date Range Picker Input -->
    <input type="text" id="daterange" />

    <!-- Initialize Date Range Picker -->
    <script>
        $(function() {
            $('#daterange').daterangepicker({
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')],
                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                },
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });
        });
    </script>
</body>
</html>

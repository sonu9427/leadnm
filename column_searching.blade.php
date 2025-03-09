<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel 5.8 Tutorial - Datatables Individual Column Searching using Ajax</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

    <script>
        var searchRoute = "{{ route('search.index') }}";

        $(document).ready(function() {
            addressTypesForm.setupData({
            tableObj: $('#product_table')
            });
        });

    </script>
   
    <script src="{{ asset('assets/js/note.js') }}"></script>  <!-- Make sure the script is loaded -->
</head>
<body>
    <div class="container">    
        <br />
        <h3 align="center">Laravel 5.8 Tutorial - Datatables Individual Column Searching using Ajax</h3>
        <br />
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="product_table">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Product Name</th>
                        <th>
                            <select name="category_filter" id="category_filter" class="form-control">
                                <option value="">Select Category</option>
                                @foreach($category as $row)
                                    <option value="{{ $row->category_id }}">{{ $row->category_name }}</option>
                                @endforeach
                            </select>
                        </th>
                        <th>Product Price</th>
                    </tr>
                </thead>
            </table>
        </div>
        <br /><br />
    </div>
</body>
</html>

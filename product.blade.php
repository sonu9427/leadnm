<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <h1>Products</h1>

    <label for="priceRange">Select Price Range:</label>
    <select id="priceRange">
        <option value="">All Prices</option>
        @for ($i = 0; $i <= 10000; $i += 500)
            @php
                $max = $i + 500 - 1; // Calculate max for the range
                $range = "{$i}-{$max}";
            @endphp
            <option value="{{ $range }}">{{ $range }}</option>
        @endfor
    </select>

    <table id="productTable" class="display">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Total Paid</th>
            </tr>
        </thead>
    </table>

    <script>
        $(document).ready(function () {
            // Initialize the DataTable and store it in the 'table' variable
            const table = $('#productTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('products.index') }}",
                    data: function (d) {
                        d.price_range = $('#priceRange').val(); // Send selected price range
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'price' },
                    { data: 'total_paid' }
                ]
            });

            // Reload DataTable when the price range changes
            $('#priceRange').change(function () {
                table.ajax.reload(); // Reload the table data
            });
        });
    </script>
</body>
</html>


{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List with Price Range Picker</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.js"></script>
</head>
<body>
    <h1>Products</h1>

    <label for="priceRange">Select Price Range:</label>
    <div id="priceRangeSlider" style="width: 15%; margin: 20px 0;"></div>
    <p>Selected Range: <span id="priceRangeValue">0 - 10000</span></p>

    <table id="productTable" class="display">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Total Paid</th>
            </tr>
        </thead>
    </table>

    <script>
        $(document).ready(function () {
            // Initialize the price range slider
            const priceRangeSlider = document.getElementById('priceRangeSlider');
            noUiSlider.create(priceRangeSlider, {
                start: [0, 10000], // Initial range
                connect: true,
                range: {
                    min: 0,
                    max: 10000
                },
                step: 100,
                tooltips: [true, true], // Show tooltips with current values
                format: {
                    to: (value) => Math.round(value),
                    from: (value) => Number(value)
                }
            });

            // Display selected range
            const priceRangeValue = document.getElementById('priceRangeValue');
            priceRangeSlider.noUiSlider.on('update', function (values) {
                priceRangeValue.innerText = `${values[0]} - ${values[1]}`;
            });

            // Initialize DataTable
            const table = $('#productTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('products.index') }}",
                    data: function (d) {
                        const range = priceRangeSlider.noUiSlider.get();
                        d.min_price = range[0];
                        d.max_price = range[1];
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'price' },
                    { data: 'total_paid' }
                ]
            });

            // Reload DataTable when the slider value changes
            priceRangeSlider.noUiSlider.on('set', function () {
                table.ajax.reload();
            });
        });
    </script>
</body>
</html> --}}


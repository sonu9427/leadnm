<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact List</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Center table on the page */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
        }

        table {
            border-collapse: collapse;
            width: 60%;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            text-align: center;
            padding: 12px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
        }
        .container {
            text-align: center;
        }

        select, button {
            margin: 10px;
            padding: 8px;
            font-size: 16px;
        }
        #projectTable th:nth-child(7),
    #projectTable td:nth-child(7) {
        width: 300px; /* Adjust the width */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left; 
    }
    
    </style>
</head>
<body>

    <div class="container">
        <h2>Contact List</h2>

        <!-- Property Type Filter -->
        <div>
            <input type="date" name="date-filter" id="date-filter">
            
            <select id="propertyTypeFilter">
                <option value="">Select Property Type</option>
                @foreach($property_types as $property_type)
                    <option value="{{ $property_type->id }}">{{ $property_type->property_type }}</option>
                @endforeach
            </select>

            <select id="projectFilter">
                <option value="">Select Project Name</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <button id="filterButton">Filter</button>
            <button id="resetButton">Reset</button>
        </div>

        <br></br>

    <table id="projectTable">
        <thead>
            <tr>
                <th>Month</th>
                <th>Date</th>
                <th>First Name</th>
                <th>Project</th>
                <th>State</th>
                <th>Property Type</th>
                <th>Product Name</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be dynamically injected by JavaScript -->
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            function loadProjectData(propertyTypeId = '',projectId = '',dateFilter = ''){
                $.ajax({
                url: "{{ route('project.list') }}",
                method: 'GET',
                data: {
                        property_type_id: propertyTypeId,
                        project_id: projectId,
                        date_filter: dateFilter,  
                    },
                success: function(response) {
                    var tableBody = $('#projectTable tbody');
                    tableBody.empty(); // Clear the table body

                    response.forEach(function(item) {
                        var row = '<tr>' +
                            '<td>' + item.month + '</td>' +
                            '<td>' + item.date + '</td>' +
                            '<td>' + item.first_name + '</td>' +
                            '<td>' + item.project + '</td>' +
                            '<td>' + item.state + '</td>' +
                            '<td>' + item.property_type + '</td>' +
                            '<td>' + item.products + '</td>' + 
                        '</tr>';
                        tableBody.append(row);
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching project data:', error);
                }
               });

            }

            loadProjectData();
            $('#filterButton').click(function() {
                var selectedPropertyType = $('#propertyTypeFilter').val();
                var selectedProject = $('#projectFilter').val();
                var selecteDateFilter = $('#date-filter').val();
                loadProjectData(selectedPropertyType,selectedProject,selecteDateFilter); 
            });

            $('#resetButton').click(function(){
                $('#propertyTypeFilter').val(''); 
                $('#projectFilter').val(''); 
                loadProjectData(); 
            });
            
        });
    </script>
</body>
</html>

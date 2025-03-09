var addressTypesForm = {
    modelEle: null,
    tableObj: null,
    formEle: null,
    formData: {},
    list: null,

    // Setup function to initialize all necessary elements and start the DataTable
    setupData: function(data) {
        this.modelEle = data?.modelEle;
        this.formEle = data?.formEle;
        this.tableObj = data?.tableObj;
        this.list = data?.list;
        this.initDataTable();
        return this;
    },

    // Initialize DataTable with specific settings
    initDataTable: function() {
        const _this = this;

        // Check if table element exists
        if (!_this.tableObj || _this.tableObj.length === 0) {
            console.error("Product table not found.");
            return;
        }

        // Initialize DataTable with necessary settings
        _this.tableObj.DataTable({
            processing: true,
            serverSide: true,
            scrollY: "50vh",
            scrollX: true,
            ajax: {
                url: searchRoute,  // URL for server-side processing
                data: function(d) {
                    // Add category filter to the DataTable request
                    d.category = $('#category_filter').val(); // Filter by category
                },
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'category_name', name: 'category_name', orderable: false },
                 { data: 'price', name: 'price' },
            ],
        });

        // Initialize category filter functionality
        _this.setupCategoryFilter();
    },

    // Setup category filter dropdown change event
    setupCategoryFilter: function() {
        const _this = this;

        // Bind the change event for category filter
        $('#category_filter').change(function() {
            _this.updateTable();
        });
    },

    // Reload DataTable with the updated category filter
    updateTable: function() {
        const _this = this;

        // Get the DataTable instance
        var table = _this.tableObj.DataTable();

        // Reload the DataTable with the new data based on selected category
        table.ajax.reload();
    }
};

// Initialize the addressTypesForm with the product table element
